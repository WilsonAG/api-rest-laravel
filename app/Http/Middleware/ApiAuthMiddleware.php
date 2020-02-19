<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        $token = preg_replace('/([\'"])/', '', $token);
        $jwtAuth = new \JwtAuth();

        $checkToken = $jwtAuth->checkToken($token);

        if ($checkToken) {
            return $next($request);
        } else {
            $response_data = array(
                'code' => 401,
                'status' => 'error',
                'message' => 'El usuario no esta autenticado.'
            );

            return response()->json($response_data, $response_data['code']);
        }
    }
}
