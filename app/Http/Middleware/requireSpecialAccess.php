<?php

namespace App\Http\Middleware;

use App\Models\Book;
use App\Models\UsersWithExtraAccess;
use Closure;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Helpers\ApiCode;

class requireSpecialAccess
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user) {
            $userId = $user->getAuthIdentifier();
            $UsersWithExtraAccess = new UsersWithExtraAccess();
            $isExtraUser = $UsersWithExtraAccess->select('*')->where('user_id', $userId)->count();

            if (!empty($isExtraUser)) {
                return $next($request);
            } else {
                return RB::error(ApiCode::FORBIDDEN);
            }
        }

        return RB::error(ApiCode::UNAUTHORIZED);
    }
}
