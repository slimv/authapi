<?php

namespace App\Seeder\Development;

use Illuminate\Database\Seeder;
use DB;
use App\Model\Permission;
use App\Model\PassportModel\PassportClient;

class HealthMobileAppPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = $this->getAuthAdminPermissionData();

        //get the client this data belong to
        $authAdminClient = PassportClient::where('name', 'Health4.0 Mobile App')->first();

        if($authAdminClient) {
        	foreach($data as $permissionData) {
	        	$permission = new Permission;
		        $permission->key = $permissionData['key'];
		        $permission->description = $permissionData['description'];
		        $permission->client_id = $authAdminClient->id;
		        $permission->save();
	        } 
        }
    }

    public function getAuthAdminPermissionData()
    {
    	$data = [
    		[
    			'key' => 'profile:change-password',
    			'description' => 'Permission to change current user password'
    		],
    		[
    			'key' => 'profile:update',
    			'description' => 'Permission to update current user profile'
    		],
    		[
    			'key' => 'purchase:reduce-price',
    			'description' => 'Permission to select special price, only for paid user'
    		]
    	];

    	return $data;
    }
}
