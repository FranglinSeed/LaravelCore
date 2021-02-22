<?php

namespace App\Http\Middleware;

use App\Models\TokenMaster;
use Closure;

class CheckToken
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
        $email = $request->header('email');
        $token = $request->header('Authorization');
        $tokenmaster = TokenMaster::where('email', $email)->where('token', $token)->first();
        if ($tokenmaster) {
            if ($tokenmaster->expire > strtotime(date("Y-m-d h:i:sa"))) {
                return $next($request);
            } else {
                TokenMaster::destroy($tokenmaster->id);
            }
        }

        return response(['noUser' => 'No such User']);
    }
}
