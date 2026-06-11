---
---

# Upgrading to v5
First, you have to find in your composer.json file the line where dashboard-starter-kit is and make sure it is changed to v5, like so:

"uneca/dashboard-starter-kit": "^5.0"

Then you can run the following command to update the kit and all its dependencies

```
composer update uneca/dashboard-starter-kit --with-all-dependencies --ignore-platform-reqs
```

Then you have to run the kit's own update command to update the kit's resources that have changed in the new version

```
php artisan chimera:update --migrations --stubs --jetstream-customizations --assets --action-classes --chimera-config
```

Then run the new migrations

```
php artisan migrate
```

## Changes to Pages

At this point, your dashboard should be functional and you should be able to sign-in. The next step is that you will need to re-add all your indicators to their respective pages as the underlying data structure that stores this info has chagned.

As map indicators and reports are now also organized under pages, you will need to create pages for them and add them to the pages. You can do this in the management section.
