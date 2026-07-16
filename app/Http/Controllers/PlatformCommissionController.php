<?php

namespace App\Http\Controllers;

use App\Models\PlatformCommissionPayment;
use App\Services\PlatformProfitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlatformCommissionController extends Controller
{
    public function account(Request $request, PlatformProfitService $service)
    {
        $company = $request->user()?->company;

        if (! $company) {
            return response()->json([
                'status' => false,
                'message' => 'حساب الشركة غير موجود.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم جلب كشف مستحقات المنصة بنجاح.',
            'data' => [
                'summary' => $service->companyStatement($company, true),
                'recent_payments' => $company->platformCommissionPayments()
                    ->latest('paid_at')
                    ->limit(10)
                    ->get(),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $company = $request->user()?->company;

        if (! $company) {
            return response()->json([
                'status' => false,
                'message' => 'حساب الشركة غير موجود.',
                'data' => null,
            ], 404);
        }

        $payments = $company->platformCommissionPayments()
            ->latest('paid_at')
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json([
            'status' => true,
            'message' => 'تم جلب دفعات الشركة للمنصة بنجاح.',
            'data' => $payments,
        ]);
    }

    /**
     * الشركة تبلغ الإدارة عن دفعة وترفع إثباتها.
     * الدفعة تبقى معلقة حتى تؤكدها الإدارة من لوحة Filament.
     */
    public function store(Request $request, PlatformProfitService $service)
    {
        $company = $request->user()?->company;

        if (! $company) {
            return response()->json([
                'status' => false,
                'message' => 'حساب الشركة غير موجود.',
                'data' => null,
            ], 404);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $statement = $service->companyStatement($company, true);
        $amount = (float) $validated['amount'];

        if ($amount > (float) $statement['available_to_submit'] + 0.009) {
            throw ValidationException::withMessages([
                'amount' => 'قيمة الدفعة أكبر من المبلغ المتاح للتسديد. الحد الأعلى هو '
                    . number_format((float) $statement['available_to_submit'], 2) . '.',
            ]);
        }

        $payment = DB::transaction(function () use ($request, $validated, $company) {
            $proofPath = $request->file('proof')
                ? $request->file('proof')->store('platform-commission-proofs', 'public')
                : null;

            return PlatformCommissionPayment::query()->create([
                'company_id' => $company->id,
                'amount' => $validated['amount'],
                'paid_at' => $validated['paid_at'],
                'payment_method' => $validated['payment_method'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'proof_path' => $proofPath,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'submission_source' => 'company',
                'submitted_by_user_id' => $request->user()->id,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال الدفعة للمراجعة. ستظهر كمقبوضة بعد تأكيد الإدارة.',
            'data' => $payment->fresh(),
        ], 201);
    }
}
