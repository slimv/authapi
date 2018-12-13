<?php
/**
 * Service to handle password changing request from server
 */

namespace App\Http\Service\Auth;

use App\Http\Model\Table\UserRepository;
use App\Http\Service\Model\UserActiveCodeService;
use App\Model\CommonResponse;
use App\Model\User;
use DB;
use App\Model\Traits\PasswordEncryptTrait;

class PasswordService
{
    use PasswordEncryptTrait;

    /** @var User repository */
    private $userRepo;
    private $userActiveCodeService;

    /**
     * AuthService constructor.
     * @param UserRepository $userRepo
     */
    public function __construct(UserRepository $userRepo, UserActiveCodeService $userActiveCodeService)
    {
        $this->userRepo = $userRepo;
        $this->userActiveCodeService = $userActiveCodeService;
    }

    /**
     * Issue code for forgot password
     * @param  [string] $email [Email of account want to have password update]
     * @return [CommonResponse]       [Response]
     */
    public function submitForgotPasswordRequest($email)
    {
        //first we need to get user with given data
        if(!$email) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        $user = User::where('email', $email)->first();
        if(!$user) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //now we generate the code
        $codeGenerataionResult = $this->userActiveCodeService->generateCode($user, 'forgot-password');

        return $codeGenerataionResult;
    }

    /**
     * Active forgot password code and set new password
     * @param  [string] $code [Code]
     * @param  [string] $password [New password]
     * @return [CommonResponse]       [Response]
     */
    public function activeForgotPasswordCode($code, $password)
    {
        if(!$code || !$password) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        DB::beginTransaction();

        try{
            //first we will try to active the code
            $activeResult = $this->userActiveCodeService->activeCode($code, 'forgot-password');

            if(!$activeResult->isSuccess()) {
                return $activeResult;
            }

            $code = $activeResult->data;

            //get the user
            $user = User::find($code->user_id);

            if(!$user) {
                DB::rollBack();
                return (new CommonResponse(400, [], __("Code contain invalid data")));
            }

            //update the password
            $data = [
                'origin_password' => $password,
                'password' => $this->cryptUserModelPassword($password)
            ];
            $updateResult = $this->userRepo->update($user, $data);

            if($updateResult->isSuccess()) {
                DB::commit();
                return (new CommonResponse(200));
            } else {
                DB::rollBack();
                return $updateResult;
            }
        } catch(\Exception $e) {
            DB::rollBack();
            return (new CommonResponse(500, [], __("Couldnt active code due to unexpected data")));
        }
    }
}