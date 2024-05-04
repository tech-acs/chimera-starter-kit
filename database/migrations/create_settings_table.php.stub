<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Uneca\Chimera\Models\Setting;

return new class extends Migration
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
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->string('label')->nullable();
            $table->boolean('directly_editable')->default(true);
            $table->timestamps();
        });
        $defaultSettings = [
            ['key' => 'color_palette', 'value' => env('COLOR_PALETTE', 'Chimera'), 'directly_editable' => 0],
            ['key' => 'app_owner_name', 'value' => env('APP_OWNER_NAME', 'ECA'), 'label' => 'App owner'],
            ['key' => 'app_owner_url', 'value' => env('APP_OWNER_URL', '#'), 'label' => "App owner url"],
        ];
        foreach ($defaultSettings as $setting) {
            Setting::create($setting);
        }
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
};
