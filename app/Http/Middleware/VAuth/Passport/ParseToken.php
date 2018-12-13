<?php

namespace App\Http\Middleware\VAuth\Passport;

use Closure;
use Illuminate\Support\Facades\Log;
use App\Auth\Providers\PassportTokenParser;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\PassportModel\PassportClient;

class ParseToken
{
    protected $parser;

    public function __construct(PassportTokenParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $key = $request->bearerToken();
        
        $decrypt = $this->parser->parseToken($key);

        if(!$decrypt || !isset($decrypt['permissions'])) {
            return (new CommonResponse(400, [], "Invalid request token"))->toMyResponse();
        }

        $clientId = $decrypt['aud'];
        $userId = $decrypt['sub'];

        $user = User::find($userId);
        $client = PassportClient::find($clientId);

        if(!$user) {
            return (new CommonResponse(400, [], "Invalid user data"))->toMyResponse();
        }
        if(!$client) {
            return (new CommonResponse(400, [], "Invalid client data"))->toMyResponse();
        }

        $request->merge([
            'jwt_user_permissions' => $decrypt['permissions'],
            'jwt_user' => $user,
            'jwt_client' => $client
        ]);

        return $next($request);
    }
}
