<?php

namespace Uneca\Chimera;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Uneca\Chimera\Commands\Chimera;

class ChimeraStarterKitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $migrations = [
            'create_area_restrictions_table',
            'create_database_connections_table',
            'create_faqs_table',
            'create_invitations_table',
            'create_maps_table',
            'create_usage_stats_table',
            'create_areas_table',
            'create_expected_values_table',
            'create_pages_table',
            'create_questionnaires_table',
            'create_indicators_table',
        ];
        $package
            ->name('chimera')
            //->hasViews() // Makes views publishable only
            ->hasConfigFile(['chimera', 'languages']) // Makes config file publishable only
            //->hasTranslations() // Makes translations publishable
            ->hasMigrations($migrations) // Makes migrations publishable only
            //->hasRoute('web')
            ->hasCommand(Chimera::class)
        ;
    }
}
