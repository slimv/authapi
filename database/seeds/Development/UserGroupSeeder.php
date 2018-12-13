<?php
namespace App\Seeder\Development;

use Illuminate\Database\Seeder;
use DB;
use Webpatser\Uuid\Uuid;
use App\Model\User;
use App\Model\Group;
use App\Model\UserGroup;
use App\Model\PassportModel\PassportClient;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = $this->getUserGroupData();

        foreach($data as $userGroupData) {
        	$user = User::where('email', $userGroupData['user_email'])->first();
            $client = PassportClient::where('name', $userGroupData['client'])->first();
        	$group = Group::where('name', $userGroupData['group'])->where('client_id', $client->id)->first();

        	if($user && $group) {
        		$userGroup = new UserGroup;
                $userGroup->group_id = $group->id;
                $userGroup->user_id = $user->id;
                $userGroup->save();
        	}
        }
    }

    public function getUserGroupData()
    {
    	$data = [
    		[
    			'user_email' => 'viet@roxwin.com',
    			'group' => 'VAuth administrator',
                'client' => 'VAuth administration control panel web app'
    		],
            [
                'user_email' => 'thang@roxwin.com',
                'group' => 'Paid user',
                'client' => 'Health4.0 Mobile App'
            ]
    	];

    	return $data;
    }
}

