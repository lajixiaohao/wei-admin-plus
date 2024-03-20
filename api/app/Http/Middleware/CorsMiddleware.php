<?php
/**
* 跨域请求处理
*
* @since 2023.7.20
*/
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as symfonyResponse;

class CorsMiddleware
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
        if ($request->isMethod('OPTIONS')) {
            $response = response(null, 204);
        } else {
            $response = $next($request);
        }

        return $this->addCorsHeaders($request, $response);
    }

    /**
     * Add CORS headers.
     */
    private function addCorsHeaders($request, $response)
    {
        $headers = [
            'Access-Control-Allow-Origin'=>'*',
            'Access-Control-Allow-Headers'=>$request->header('access-control-request-headers'),
            'Access-Control-Allow-Methods'=>'GET,POST,PATCH,DELETE,OPTIONS',
            // 'Access-Control-Allow-Credentials'=>true
        ];

        if ($response instanceof JsonResponse) {
            foreach ($headers as $key => $value) {
                $response->header($key, $value);
            }
            return $response;
        }

        if ($response instanceof symfonyResponse) {
            // 兼容axios headers接受不到Content-Disposition问题
            // $headers['Access-Control-Expose-Headers'] = 'Content-Disposition';
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
            return $response;
        }

        return $response;
    }
}