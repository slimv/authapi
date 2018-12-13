<?php

namespace App\Seeder\Development;

use Illuminate\Database\Seeder;
use DB;
use App\Model\PassportModel\PassportClient;

class HealthMobileAppPasswordGrantClientSeeder extends Seeder
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
        $healthMobileAppClient->name = "Health4.0 Mobile App";
        $healthMobileAppClient->secret = 'y8YEUyE7CGDyg0at8n9IajPczaGi9NPuRhzqZXKDljvGoEWuDw4aW';
        $healthMobileAppClient->redirect = "http://localhost";
        $healthMobileAppClient->personal_access_client = 0;
        $healthMobileAppClient->password_client = 1;
        $healthMobileAppClient->revoked = 0;
        $healthMobileAppClient->save();
    }
}

