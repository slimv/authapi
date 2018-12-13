<?php

namespace App\Seeder\Development;

use Illuminate\Database\Seeder;
use DB;
use Webpatser\Uuid\Uuid;
use App\Model\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User;
        $user->email = "viet@roxwin.com";
        $user->password = bcrypt('1234567');
        $user->first_name = "Viet";
        $user->last_name = "Quoc";
        $user->status = 'actived';
        $user->save();

        $user = new User;
        $user->email = "thang@roxwin.com";
        $user->password = bcrypt('1234567');
        $user->first_name = "Thang";
        $user->last_name = "Do";
        $user->status = 'actived';
        $user->save();
    }
}
