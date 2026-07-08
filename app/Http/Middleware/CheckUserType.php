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
                'status' => false,
                'message' => 'Unauthorized',
                'data' => null,
            ], 401);
        }

        if ($user->user_type !== $type) {
            return response()->json([
                'status' => false,
                'message' => 'غير مسموح لك بالدخول',
                'data' => [
                    'required_type' => $type,
                    'your_type' => $user->user_type,
                ],
            ], 403);
        }

        return $next($request);
    }
}
