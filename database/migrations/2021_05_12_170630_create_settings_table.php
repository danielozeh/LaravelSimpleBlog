<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_title')->nullable();
            $table->text('site_description')->nullable();
            $table->text('site_keywords')->nullable();
            $table->string('course_default_image')->nullable();
            $table->text('phone_number_1')->nullable();
            $table->text('phone_number_2')->nullable();
            $table->text('email')->nullable();
            $table->text('address')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('telegram_url')->nullable();
            $table->text('about_us')->nullable();
            $table->text('our_mission')->nullable();
            $table->text('our_vision')->nullable();
            $table->text('privacy_policy')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('legal')->nullable();
            $table->string('home_title')->nullable();
            $table->string('header_logo')->nullable();
            $table->string('footer_logo')->nullable();
            $table->string('mobile_logo')->nullable();
            $table->timestamps();
        });

        DB::table('settings')->insert(
            array(
                'site_title' => 'Simple Blog',
                'site_description' => 'Simple Blog',
                'site_keywords' => '',
                'course_default_image' => '',
                'phone_number_1' => '',
                'phone_number_2' => '',
                'email' => 'hello@danielozeh.com.ng',
                'address' => '',
                'facebook_url' => '',
                'youtube_url' => '',
                'twitter_url' => '',
                'instagram_url' => '',
                'telegram_url' => '',
                'about_us' => '',
                'our_mission' => '',
                'our_vision' => '',
                'privacy_policy' => '',
                'terms_conditions' => '',
                'legal' => '',
                'home_title' => '',
                'header_logo' => '',
                'footer_logo' => '',
                'mobile_logo' => ''
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
        Schema::dropIfExists('settings');
    }
}
