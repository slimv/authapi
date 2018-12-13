<?php
/**
 * Service to handle user active code
 */

namespace App\Http\Service\Model;

use App\Http\Model\Table\UserActiveCodeRepository;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\UserActiveCode;
use VStringGenerator;
use VDateTime;

class UserActiveCodeService
{
    /** @var UserActiveCodeRepository */
    private $userActiveCodeRepo;

    /**
     * AuthService constructor.
     * @param UserActiveCodeRepository $userActiveCodeRepo
     */
    public function __construct(UserActiveCodeRepository $userActiveCodeRepo)
    {
        $this->userActiveCodeRepo = $userActiveCodeRepo;
    }

    /**
     * Generate code for selected user, for selected type
     * @param  [User] $user [Selected user]
     * @param  [string] $type [Code type]
     * @return [CommonResponse]       [Generated result]
     */
    public function generateCode($user, $type)
    {
        if(!$user || ! $type) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //make sure there isnt any code exited but havent expired yet
        $now = VDateTime::now();
        $exitedCode = UserActiveCode::where('user_id', $user->id)->where('type', $type)->where('status', 'waiting-active')->where('expire_at', '>', $now)->first();
        if($exitedCode) {
            return (new CommonResponse(400, [], __("You already have a code for this request and havent use it yet")));
        }

        //first we generate the code
        $code = null;
        if($type == 'signup') {
            $code = VStringGenerator::generateStringFromTemplate(env('SECURITY_SIGNUP_CODE_FORMAT'));
        } else if($type == 'forgot-password') {
            $code = VStringGenerator::generateStringFromTemplate(env('SECURITY_FORGOT_PASS_CODE_FORMAT'));
        }
        
        if(!$code) {
            return (new CommonResponse(400, [], __("Invalid code request")));
        }

        $codeData = [
            'user_id' => $user->id,
            'type' => $type,
            'code' => $code,
            'expire_at' => VDateTime::now()->addSeconds(env('SECURITY_CODE_LIFE_TIME')),
        ];

        //next we create using those data
        $createResult = $this->userActiveCodeRepo->create($codeData);

        return $createResult;
    }

    /**
     * Function to active the signup code
     * @param  [string] $code [Selected code]
     * @return [CommonResponse]       [Result]
     */
    public function activeCode($code, $type)
    {
        if(!$code || !$type) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //get the code
        $activeCode = $this->userActiveCodeRepo->getCode($code, $type);

        if(!$activeCode) {
            return (new CommonResponse(400, [], __("The code you provide is invalid")));
        }

        if($activeCode->isUsed()) {
            return (new CommonResponse(400, [], __("This code have been used")));
        }

        if($activeCode->isExpired()) {
            return (new CommonResponse(400, [], __("The requested code have been expired")));
        }

        $activeResult = $activeCode->activeCode();

        if($activeResult) {
            return (new CommonResponse(200, $activeCode));
        } else {
            return (new CommonResponse(500, [], __("Cannot active the code due to unexpect error")));
        }
    }
}