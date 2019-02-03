<?php

namespace App\Http\Middleware;

use Closure;

class RecordLastActivedTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::check()) {
            //记录时间
            \Auth::user()->recordLastActivedAt();
        }
        return $next($request);
    }
}
