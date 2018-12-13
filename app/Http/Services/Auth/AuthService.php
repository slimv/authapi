<?php
/**
 * Service to handle authentication request from user
 */

namespace App\Http\Service\Auth;

use App\Http\Model\Table\UserRepository;
use App\Http\Service\Model\UserActiveCodeService;
use App\Model\Traits\PasswordEncryptTrait;
use App\Model\CommonResponse;
use App\Model\User;
use DB;

class AuthService
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
     * Function to signup new user into Vauth system.
     * @param  [array] $data [User request data]
     * @return [CommonResponse]       [Response for the signup process]
     */
    public function signUp($data)
    {
        if(!$data) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        //first we need to remove uneccessary data
        $data = $this->userRepo->stripPropertiesFromRequestData($data);

        //then we need to crypt the password data
        if(isset($data['password'])) {
            $data['origin_password'] = $data['password'];
            $data['password'] = $this->cryptUserModelPassword($data['password']);
        }

        //next we create using those data
        DB::beginTransaction();

        try{
            $createResult = $this->userRepo->create($data);

            //now we need to create activation code for this user
            if($createResult->isSuccess()) {
                $createdUser = $createResult->data;
                $codeGenerataionResult = $this->userActiveCodeService->generateCode($createdUser, 'signup');
                if(!$codeGenerataionResult->isSuccess()) {
                    DB::rollBack();
                    return $codeGenerataionResult;
                }
            }

            DB::commit();
            
            return $createResult;
        } catch(\Exception $e) {
            DB::rollBack();
            return (new CommonResponse(500, [], __("Couldnt signup due to unexpected data")));
        }
    }

    /**
     * Active signup code
     * @param  [string] $code [Code]
     * @return [CommonResponse]       [Response]
     */
    public function activeSignupCode($code)
    {
        DB::beginTransaction();

        try{
            //first we will try to active the code
            $activeResult = $this->userActiveCodeService->activeCode($code, 'signup');

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

            $activeResult = $user->activeUser();

            if($activeResult) {
                DB::commit();
                return (new CommonResponse(200));
            } else {
                DB::rollBack();
                return (new CommonResponse(500, [], __("Cannot active the code due to unexpect error")));
            }
        } catch(\Exception $e) {
            DB::rollBack();
            return (new CommonResponse(500, [], __("Couldnt active code due to unexpected data")));
        }
    }
}