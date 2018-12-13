<?php

use Illuminate\Database\Seeder;
use App\Seeder\Development;
use App\Seeder\General;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //General seeding will always be called in both production and development
        $this->call(General\AuthAdminPasswordGrantClientSeeder::class);
        $this->call(General\AuthAdminPermissionSeeder::class);
        $this->call(General\AuthAdminGroupSeeder::class);

    	//do not call there seeding in production
    	if(config('app.env') != 'production') {
    		$this->call(Development\UsersSeeder::class);
    		$this->call(Development\HealthMobileAppPasswordGrantClientSeeder::class);
            $this->call(Development\HealthMobileAppPermissionSeeder::class);
            $this->call(Development\HealthMobileAppGroupSeeder::class);
            $this->call(Development\UserGroupSeeder::class);
    	}
    }
}
