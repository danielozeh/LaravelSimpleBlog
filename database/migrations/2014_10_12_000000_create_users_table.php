<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignId('role_id')->constrained('roles')->default(3);
            $table->integer('is_active')->default('1');
            $table->integer('is_verified')->default('0');
            $table->ipAddress('ip_address')->nullable();
            $table->string('verification_code', 40);
            $table->string('balance', 40)->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->insert(
            array(
                'first_name' => 'Daniel',
                'last_name' => 'Ozeh',
                'email' => 'hello@danielozeh.com.ng',
                'password' => bcrypt('password'),
                'role_id' => 1,
                'is_active' => 1,
                'is_verified' => 1,
                'verification_code' => 'abcdefgh'
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
