---
---

# The update command

The update command greatly aids in updating the version of Dashboard Starter Kit you are running.

```
php artisan chimera:update [options]
```

It has the following options, which you might use depending on what is recommended in the release notes for each update.

- --all : runs all tasks (this is almost like a new installation and there is little chance that you will ever use this)
- --chimera-config : will re-publish the chimera.php config file to the config directory. This is needed in cases where we add a new configuration in the future
- --migrations : will re-publish any new database migrations that might have been added to the kit since installation
- --packages : will install any new composer packages that might have been added to the kit since installation
- --action-classes : will copy available action classes form the package to the app/Actions directory
- --jetstream-modifications : will re-publish customized Laravel Jetstream views and actions
- --assets : will re-publish resources (js, css, stubs, tailwind.config.js and vite.config.js)
- --color-palettes : re-publishes the color-palettes directory
- --stubs : will re-publish stubs used in the various chimera:make commands
- --npm : will update the application's package.json with required npm packages
- --copy-env : publishes .env.example and .env file and generates a new app key

In the example below, all js, css and image files will be published and this might be necessary if a new version has modified any Javascript, CSS or stub files.

```
php artisan chimera:update --assets
```

:::caution
Please be aware that when you re-publish previously published resources, changes you might have made to those resources after installation will likely be overwritten. 
Under normal circumstances, you will likely not have modified any of these resources but be aware that this might happen.

For example, the --copy-env option would overwrite the previous .env file, if you have credentials in there that you have not saved elsewhere, those will be lost.
:::