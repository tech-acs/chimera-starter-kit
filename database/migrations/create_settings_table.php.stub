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
            $table->string('input_type')->default('number');
            $table->string('group')->nullable();
            $table->integer('rank')->nullable();
            $table->boolean('directly_editable')->default(true);
            $table->timestamps();
        });
        $defaultSettings = collect([
            ['key' => 'color_palette',
                'value' => env('COLOR_PALETTE', 'Chimera'),
                'directly_editable' => false],

            ['key' => 'app_owner_name',
                'value' => env('APP_OWNER_NAME', 'ECA'),
                'label' => 'App owner|The name of your organization. Will be displayed in the footer.',
                'input_type' => 'text',
                'group' => 'App ownership|Affects what is displayed in the footer.'],
            ['key' => 'app_owner_url',
                'value' => env('APP_OWNER_URL', '#'),
                'label' => "App owner url|The website of your organization. Will be linked to in the footer.",
                'input_type' => 'text',
                'group' => 'App ownership|Affects what is displayed in the footer.'],

            ['key' => 'indicators_per_page',
                'value' => env('INDICATORS_PER_PAGE', 2),
                'label' => "Indicators per page|The number of indicators to display per page.",
                'input_type' => 'number',
                'group' => 'Per page limits|Settings related to pagination and page sizes.'],
            ['key' => 'records_per_page',
                'value' => env('RECORDS_PER_PAGE', 20),
                'label' => "Records per table page|The number of records to display per page in tables (in management screens).",
                'input_type' => 'number',
                'group' => 'Per page limits|Settings related to pagination and page sizes.'],
            ['key' => 'featured_indicators_per_data_source',
                'value' => env('FEATURED_INDICATORS_PER_DATA_SOURCE', 20),
                'label' => "Featured indicators per data source|The number of indicators to display on the homepage, featured indicators section.",
                'input_type' => 'number',
                'group' => 'Per page limits|Settings related to pagination and page sizes.'],

            ['key' => 'show_map_menu',
                'value' => env('MAP_MENU', 'on'),
                'label' => "Show Map menu|Whether to show the Map menu in the main navbar or not.",
                'input_type' => 'checkbox',
                'group' => 'Map|Settings related to map behavior.'],
            ['key' => 'map_center_lat',
                'value' => env('MAP_CENTER_LAT', 9.005401),
                'label' => "Map center (latitude)|Use decimal degree format, e.g. 9.005401",
                'input_type' => 'number',
                'group' => 'Map|Settings related to map behavior.'],
            ['key' => 'map_center_lon',
                'value' => env('MAP_CENTER_LON', 38.763611),
                'label' => "Map center (longitude)|Use decimal degree format, e.g. -3.763732",
                'input_type' => 'number',
                'group' => 'Map|Settings related to map behavior.'],
            ['key' => 'map_starting_zoom',
                'value' => env('MAP_STARTING_ZOOM', 6),
                'label' => "Map starting zoom level|Initial zoom level of map. A number between 1 and 20.",
                'input_type' => 'number',
                'group' => 'Map|Settings related to map behavior.'],

            ['key' => 'show_reports_menu',
                'value' => env('REPORTS_MENU', 'on'),
                'label' => "Show Reports menu|Whether to show the Reports menu in the main navbar or not.",
                'input_type' => 'checkbox',
                'group' => 'Reports|Settings related to report behavior.'],

            ['key' => 'show_area_insights_menu',
                'value' => env('AREA_INSIGHTS_MENU', 'on'),
                'label' => "Show Area Insights menu|Whether to show the Area Insights menu in the main navbar or not.",
                'input_type' => 'checkbox',
                'group' => 'Area insights|Settings related to area insights behavior.'],

            ['key' => 'mail_enabled',
                'value' => env('MAIL_ENABLED', 'off'),
                'label' => "Enable email sending via SMTP|If enabled, emails will be sent by default via SMTP using the details below",
                'input_type' => 'checkbox',
                'group' => 'Mail|Settings related to email sending via SMTP.'],
            ['key' => 'mail_host',
                'value' => env('MAIL_HOST', '127.0.0.1'),
                'label' => "Host|SMTP server IP address or host name",
                'input_type' => 'text',
                'group' => 'Mail|Settings related to email sending via SMTP.'],
            ['key' => 'mail_port',
                'value' => env('MAIL_PORT', 2525),
                'label' => "Port|Outgoing SMTP port number.",
                'input_type' => 'number',
                'group' => 'Mail|Settings related to email sending via SMTP.'],
            ['key' => 'mail_username',
                'value' => env('MAIL_USERNAME'),
                'label' => "Username|",
                'input_type' => 'text',
                'group' => 'Mail|Settings related to email sending via SMTP.'],
            ['key' => 'mail_password',
                'value' => env('MAIL_PASSWORD'),
                'label' => "Password|",
                'input_type' => 'text',
                'group' => 'Mail|Settings related to email sending via SMTP.'],
            ['key' => 'mail_encryption',
                'value' => env('MAIL_ENCRYPTION'),
                'label' => "Encryption|Encryption method. Usually TLS or SSL.",
                'input_type' => 'text',
                'group' => 'Mail|Settings related to email sending via SMTP.'],
            ['key' => 'mail_from_address',
                'value' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'label' => "From address|The email address to send from",
                'input_type' => 'text',
                'group' => 'Mail|Settings related to email sending via SMTP.'],
            ['key' => 'mail_from_name',
                'value' => env('MAIL_FROM_NAME'),
                'label' => "From name|The display name recipients see",
                'input_type' => 'text',
                'group' => 'Mail|Settings related to email sending via SMTP.'],
        ])->map(function ($setting) {
            $setting['value'] = Crypt::encryptString($setting['value']);
            return $setting;
        });

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
