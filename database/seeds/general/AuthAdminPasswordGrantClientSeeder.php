<?php

namespace App\Seeder\General;

use Illuminate\Database\Seeder;
use DB;
use App\Model\PassportModel\PassportClient;

class AuthAdminPasswordGrantClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	//Health4.0 Administration control panel web app
        $healthMobileAppClient = new PassportClient;
        $healthMobileAppClient->name = "VAuth administration control panel web app";
        $healthMobileAppClient->secret = env("DB_AUTH_CLIENT_SECRET");
        $healthMobileAppClient->redirect = "http://localhost";
        $healthMobileAppClient->personal_access_client = 0;
        $healthMobileAppClient->password_client = 1;
        $healthMobileAppClient->revoked = 0;
        $healthMobileAppClient->save();

        //this client will only be used by client backgroun process app (DO NOT LET USER USE THIS)
        $adminBackgroundApiClientGrant = new PassportClient;
        $adminBackgroundApiClientGrant->name = "VAuth Trust Client Grant";
        $adminBackgroundApiClientGrant->secret = env("DB_AUTH_BACKGROUND_CLIENT_SECRET");
        $adminBackgroundApiClientGrant->redirect = "";
        $adminBackgroundApiClientGrant->personal_access_client = 0;
        $adminBackgroundApiClientGrant->password_client = 0;
        $adminBackgroundApiClientGrant->revoked = 0;
        $adminBackgroundApiClientGrant->save();
    }
}
