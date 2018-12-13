<?php
/**
 * Service to handle authentication request from user using facebook
 */

namespace App\Http\Service\FbAuth;

use App\Http\Model\Table\FbUserRepository;
use App\Http\Service\Model\UserActiveCodeService;
use App\Model\Traits\PasswordEncryptTrait;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\PassportModel\PassportClient;
use DB;
use Illuminate\Support\Facades\Log;
use Socialite;
use App\Model\Traits\PassportUserTokenTrait;

class FbAuthService
{
    use PasswordEncryptTrait, PassportUserTokenTrait;

    /** @var User repository */
    private $userRepo;
    private $userActiveCodeService;
    private $fb;

    /**
     * AuthService constructor.
     * @param UserRepository $userRepo
     */
    public function __construct(FbUserRepository $userRepo, UserActiveCodeService $userActiveCodeService)
    {
        $this->userRepo = $userRepo;
        $this->userActiveCodeService = $userActiveCodeService;
        $this->fb = Socialite::driver('facebook')->scopes(['email']);
    }

    /**
     * Function to authentication user login using facebook data
     * @param  [array] $data [User request data]
     * @return [CommonResponse]       [Response for the signup process]
     */
    public function auth($data)
    {
        if(!$data || !isset($data['token']) || !isset($data['secret'])) {
            return (new CommonResponse(400, [], __("Invalid request data")));
        }

        try{
            $token = $data['token'];
            $secret = $data['secret'];

            $clientResult = $this->getClientBySecret($secret);
            if(!$clientResult->isSuccess()) {
                return $clientResult;
            }
            $client = $clientResult->data;

            $facebookUser = $this->parseToken($token);
            if(!$facebookUser) {
                //couldnt get facebook user data
                return (new CommonResponse(400, [], __("Invalid facebook user data. Cannot retrieve user information from provided token.")));
            }

            //if we get facebook user
            //first we need to check if there is any user with current facebook id
            $userResult = $this->getUserByFacebookUser($facebookUser);
            if(!$userResult->isSuccess()) {
                return $userResult;
            }

            $user = $userResult->data;
            if($user) {
                //login
                $tokenResponse = $this->generatePassportToken($user, $client);
                return (new CommonResponse(200, $tokenResponse));
            } else {
                //signup
                $signUpResult = $this->signupWithFacebookUser($facebookUser);
                if(!$signUpResult->isSuccess()) {
                    return $signUpResult;
                }

                //get the user
                $user = $signUpResult->data;
                $tokenResponse = $this->generatePassportToken($user, $client);
                return (new CommonResponse(200, $tokenResponse));
            }

            return (new CommonResponse(200, $user));
        } catch(\Exception $e) {
            Log::info(['data' => $e->getMessage()]);
            return (new CommonResponse(500, [], __("Couldnt sign in due to unexpected data")));
        }
    }

    /**
     * Function to parse token and return facebook user data
     * @param  [string] $token [Facebook token]
     * @return [array]        [Facebook data]
     */
    public function parseToken($token)
    {
        $facebookUser = $this->fb->userFromToken($token);
        return $facebookUser;
    }

    /**
     * Retrieve user from facebook user, if there isnt an user, return null
     * @param  [type] $facebookUser [description]
     * @return [type]               [description]
     */
    public function getUserByFacebookUser($facebookUser)
    {
        if(!$facebookUser) {
            return (new CommonResponse(400, [], __("Invalid facebook user data.")));
        }

        if(!$facebookUser->id) {
            return (new CommonResponse(400, [], __("Invalid facebook user data, cannot retrieve user id")));
        }

        $user = User::withTrashed()->where('facebook_id', $facebookUser->id)->first();
        if(!$user) {
            return (new CommonResponse(200, null));
        } else {
            if($user->deleted_at) {
                return (new CommonResponse(423, [], __("User have been temporary locked.")));
            } else {
                return (new CommonResponse(200, $user));
            }
        }
    }

    /**
     * Retrieve client by secret
     * @param  [type] $facebookUser [description]
     * @return [type]               [description]
     */
    public function getClientBySecret($secret)
    {
        if(!$secret) {
            return (new CommonResponse(400, [], __("Invalid client data.")));
        }

        $client = PassportClient::where('secret', $secret)->first();
        if(!$client) {
            return (new CommonResponse(400, [], __("Invalid client data.")));
        } else {
            return (new CommonResponse(200, $client));
        }
    }

    /**
     * Signup with facebook user data
     * @param  [type] $facebookUser [description]
     * @return [type]               [description]
     */
    public function signupWithFacebookUser($facebookUser)
    {
        if(!$facebookUser->email) {
            //if we cannot get facebook email, we will stop the process since email is essential for our system
            return (new CommonResponse(400, [], __("Your facebook authentication have successfully processed but we cannot login into the system because your facebook account dont have a valid email. Please update your facebook email and try again.")));
        }

        //there are 2 cases: user with this email already existed or email not existed yet
        $user = User::withTrashed()->where('email', $facebookUser->email)->first();
        if(!$user) {
            $newUserData = [
                "email" => $facebookUser->email,
                "facebook_id" => $facebookUser->id,
                "first_name" => $facebookUser->name,
                "status" => "actived"
            ];

            $createResult = $this->userRepo->create($newUserData);
            return $createResult;
        } else {
            if($user->deleted_at) {
                return (new CommonResponse(423, [], __("User have been temporary locked.")));
            } else {
                //need to update this user with this facebook id
                $user->facebook_id = $facebookUser->id;
                $saveResult = $user->save();

                if($saveResult) {
                    return (new CommonResponse(200, $user));
                }
            }
        }
    }
}