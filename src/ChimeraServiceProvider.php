<?php

namespace Uneca\Chimera;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Uneca\Chimera\Commands\Adminify;
use Uneca\Chimera\Commands\Chimera;
use Uneca\Chimera\Commands\PermissionsToDb;
use Uneca\Chimera\Commands\ShpToDb;

class ChimeraServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $migrations = ['add_title_and_organization_columns_to_users_table',
            'create_area_restrictions_table',
            'create_database_connections_table',
            'create_faqs_table',
            'create_invitations_table',
            'create_maps_table',
            'create_usage_stats_table',
        ];
        $package
            ->name('chimera')
            ->hasViews() // Makes views publishable only
            ->hasConfigFile('chimera') // Makes config file publishable only
            ->hasTranslations() // Makes translations publishable
            ->hasMigrations($migrations) // Makes migrations publishable only
            //->hasRoute('web')
            ->hasCommands([Adminify::class, Chimera::class, PermissionsToDb::class, ShpToDb::class])
        ;
    }
}
