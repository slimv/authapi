<?php

namespace App\Seeder\Development;

use Illuminate\Database\Seeder;
use DB;
use App\Model\Group;
use App\Model\Permission;
use App\Model\GroupPermission;
use App\Model\PassportModel\PassportClient;

class HealthMobileAppGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = $this->getHealthMobileAppGroupData();

        //get the client this data belong to
        $authAdminClient = PassportClient::where('name', 'Health4.0 Mobile App')->first();

        if($authAdminClient) {
        	foreach($data as $groupData) {
	        	$group = new Group;
		        $group->name = $groupData['name'];
                $group->key = $groupData['key'];
		        $group->description = $groupData['description'];
                $group->client_id = $authAdminClient->id;

                if(isset($groupData['parent'])) {
                    $parent = Group::where('name', $groupData['parent'])->first();
                    $group->parent_id = $parent->id;
                }

		        $group->save();

                foreach($groupData['permissions'] as $permissionKey) {
                    $permission = Permission::where('key', $permissionKey)->where('client_id', $authAdminClient->id)->first();
                    if($permission) {
                        $groupPermission = new GroupPermission;
                        $groupPermission->group_id = $group->id;
                        $groupPermission->permission_id = $permission->id;
                        $groupPermission->save();
                    }
                }
	        } 
        }
    }

    public function getHealthMobileAppGroupData()
    {
    	$data = [
            [
                'name' => 'Paid user',
                'key' => 'health:paid_user',
                'description' => 'User who paid to use Health App',
                'permissions' => [
                    'purchase:reduce-price'
                ]
            ],
    		[
    			'name' => 'Normal user',
                'key' => 'health:normal_user',
    			'description' => 'User who registed to use Health App',
                'parent' => 'Paid user',
                'permissions' => [
                    'profile:change-password', 
                    'profile:update'
                ]
    		]
    	];

    	return $data;
    }
}

