<?php
/**
 * Service to handle authentication request from user using google
 */

namespace App\Http\Service\GoogleAuth;

use App\Http\Model\Table\GoogleUserRepository;
use App\Http\Service\Model\UserActiveCodeService;
use App\Model\Traits\PasswordEncryptTrait;
use App\Model\CommonResponse;
use App\Model\User;
use App\Model\PassportModel\PassportClient;
use DB;
use Illuminate\Support\Facades\Log;
use Socialite;
use App\Model\Traits\PassportUserTokenTrait;

class GoogleAuthService
{
    use PasswordEncryptTrait, PassportUserTokenTrait;

    /** @var User repository */
    private $userRepo;
    private $userActiveCodeService;
    private $google;

    /**
     * AuthService constructor.
     * @param UserRepository $userRepo
     */
    public function __construct(GoogleUserRepository $userRepo, UserActiveCodeService $userActiveCodeService)
    {
        $this->userRepo = $userRepo;
        $this->userActiveCodeService = $userActiveCodeService;
        $this->google = Socialite::driver('google');
    }

    /**
     * Function to authentication user login using google data
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

            $googleUser = $this->parseToken($token);
            if(!$googleUser) {
                //couldnt get google user data
                return (new CommonResponse(400, [], __("Invalid google user data. Cannot retrieve user information from provided token.")));
            }

            //if we get google user
            //first we need to check if there is any user with current google id
            $userResult = $this->getUserByGoogleUser($googleUser);
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
                $signUpResult = $this->signupWithGoogleUser($googleUser);
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
     * Function to parse token and return Google user data
     * @param  [string] $token [Google token]
     * @return [array]        [Google data]
     */
    public function parseToken($token)
    {
        $googleUser = $this->google->userFromToken($token);
        return $googleUser;
    }

    /**
     * Retrieve user from google user, if there isnt an user, return null
     * @param  [type] $googleUser [description]
     * @return [type]               [description]
     */
    public function getUserByGoogleUser($googleUser)
    {
        if(!$googleUser) {
            return (new CommonResponse(400, [], __("Invalid google user data.")));
        }

        if(!$googleUser->id) {
            return (new CommonResponse(400, [], __("Invalid google user data, cannot retrieve user id")));
        }

        $user = User::withTrashed()->where('google_id', $googleUser->id)->first();
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
     * @param  [type] $googleUser [description]
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
     * Signup with google user data
     * @param  [type] $googleUser [description]
     * @return [type]               [description]
     */
    public function signupWithGoogleUser($googleUser)
    {
        if(!$googleUser->email) {
            //if we cannot get google email, we will stop the process since email is essential for our system
            return (new CommonResponse(400, [], __("Your google authentication have successfully processed but we cannot login into the system because your google account dont have a valid email. Please update your google email and try again.")));
        }

        //there are 2 cases: user with this email already existed or email not existed yet
        $user = User::withTrashed()->where('email', $googleUser->email)->first();
        if(!$user) {
            $newUserData = [
                "email" => $googleUser->email,
                "google_id" => $googleUser->id,
                "first_name" => $googleUser->name,
                "status" => "actived"
            ];

            $createResult = $this->userRepo->create($newUserData);
            return $createResult;
        } else {
            if($user->deleted_at) {
                return (new CommonResponse(423, [], __("User have been temporary locked.")));
            } else {
                //need to update this user with this google id
                $user->google_id = $googleUser->id;
                $saveResult = $user->save();

                if($saveResult) {
                    return (new CommonResponse(200, $user));
                }
            }
        }
    }
}