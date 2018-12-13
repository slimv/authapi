<?php

namespace App\Seeder\General;

use Illuminate\Database\Seeder;
use DB;
use App\Model\Group;
use App\Model\Permission;
use App\Model\GroupPermission;
use App\Model\PassportModel\PassportClient;

class AuthAdminGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = $this->getAuthAdminGroupData();

        //get the client this data belong to
        $authAdminClient = PassportClient::where('name', 'VAuth administration control panel web app')->first();

        if($authAdminClient) {
        	foreach($data as $groupData) {
	        	$group = new Group;
		        $group->name = $groupData['name'];
                $group->key = $groupData['key'];
		        $group->description = $groupData['description'];
                $group->client_id = $authAdminClient->id;
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

    public function getAuthAdminGroupData()
    {
    	$data = [
    		[
                'key' => 'vauth:administrator',
    			'name' => 'VAuth administrator',
    			'description' => 'People belong to this group are Vauth administrators who have full permission on the system',
                'permissions' => [
                    'auth:access',
                    'client:view', 
                    'client:create',
                    'client:update',
                    'client:view-deleted',
                    'client:regenerate-secret',
                    'client:lock',
                    'client:unlock',
                    'client:delete',
                    'client:permission-view-deleted',
                    'user:view',
                    'user:view-deleted',
                    'user:update',
                    'user:lock',
                    'user:unlock',
                    'user:delete'
                ]
    		]
    	];

    	return $data;
    }
}
