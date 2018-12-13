<?php

namespace App\Seeder\General;

use Illuminate\Database\Seeder;
use DB;
use App\Model\Permission;
use App\Model\PassportModel\PassportClient;

class AuthAdminPermissionSeeder extends Seeder
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
        $authAdminClient = PassportClient::where('name', 'VAuth administration control panel web app')->first();

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
                'key' => 'auth:access',
                'description' => 'Permission to access vauth control panel'
            ],
    		[
    			'key' => 'client:view',
    			'description' => 'Permission to view all clients in Auth system'
    		],
    		[
    			'key' => 'client:create',
    			'description' => 'Permission to create client'
    		],
            [
                'key' => 'client:update',
                'description' => 'Permission to update client. Also this permission allow user to create/change group, permission in client.'
            ],
            [
                'key' => 'client:regenerate-secret',
                'description' => 'Permission to generate secret for client'
            ],
            [
                'key' => 'client:lock',
                'description' => 'Permission to disable client'
            ],
            [
                'key' => 'client:unlock',
                'description' => 'Permission to reactive client'
            ],
            [
                'key' => 'client:delete',
                'description' => 'Permission to permanently remove client'
            ],
            [
                'key' => 'client:view-deleted',
                'description' => 'Permission to view deleted clients'
            ],
            [
                'key' => 'client:permission-view-deleted',
                'description' => 'Permission to view group or permission which got deleted'
            ],
    		[
    			'key' => 'user:view',
    			'description' => 'Permission to view all users in Auth system'
    		],
            [
                'key' => 'user:view-deleted',
                'description' => 'Permission to view deleted users in Auth system'
            ],
            [
                'key' => 'user:update',
                'description' => 'Permission to update user'
            ],
            [
                'key' => 'user:lock',
                'description' => 'Permission to disable user'
            ],
            [
                'key' => 'user:unlock',
                'description' => 'Permission to reactive user'
            ],
            [
                'key' => 'user:delete',
                'description' => 'Permission to permanently remove user'
            ],
    	];

    	return $data;
    }
}
