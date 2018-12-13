<?php

namespace App\Http\Middleware\VAuth\Permission;

use Closure;
use Illuminate\Support\Facades\Log;
use App\Model\CommonResponse;
use App\Traits\PermissionTrait;

class CheckVerified
{
    use PermissionTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $result = $this->isSessionVerified($request);

        if($result->code == 200) {
            return $next($request);
        } else {
            return $result->toMyResponse();
        }
    }
}
