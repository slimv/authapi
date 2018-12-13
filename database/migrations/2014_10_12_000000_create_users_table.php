<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->uuid('id');
                $table->primary('id');
                $table->string('email', 250)->unique();
                $table->string('facebook_id', 250)->unique()->nullable();
                $table->string('google_id', 250)->unique()->nullable();
                $table->string('password', 100)->nullable();
                $table->string('first_name', 250);
                $table->string('last_name', 250)->nullable();
                $table->string('status')->nullable()->default('waiting-active');          //actived, deactived, waiting-active
                $table->timestamp('last_access_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
