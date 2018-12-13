<?php

namespace App\Http\Controllers\VAuth\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CommonResponse;
use App\Http\Service\Auth\ProfileService;

class ProfileController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Profile Controller
    |--------------------------------------------------------------------------
    |
    | Controller to handle user profile
    |
    */
    private $profileService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Api to get current user profile
     * @param  Request $request []
     * @return [type]           [description]
     */
    public function myProfile(Request $request)
    {
        $this->profileService->setJwtSessionFromRequest($request);
        $result = $this->profileService->getCurrentUserProfile();

        return $result->toMyResponse();
    }
}
