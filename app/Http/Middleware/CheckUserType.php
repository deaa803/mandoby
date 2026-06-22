<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($user->user_type !== $type) {
            return response()->json([
                'message' => 'غير مسموح لك بالدخول يا حبيبي',
                'required_type' => $type,
                'your_type' => $user->user_type,
            ], 403);
        }

        return $next($request);
    }
}
