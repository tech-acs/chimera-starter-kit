---
---

# Caching

The Dashboard Starter Kit comes with a complete caching strategy built-in.

Caching of results is always happening behind the scenes. Every published indicator, scorecard, case stat and map indicator will be cached for a set amount of time that is determined by the CACHE_TTL_SECONDS setting in your .env file. The default value of the caching time is **thirty minutes**.

You will most likely want to use your own caching strategy that is appropriate to your data size and other needs. You will therefore need to schedule tasks to update these caches regularly. This is achieved by using the ```chimera:cache``` group of commands. You can run them manually as such but you should schedule them using Laravel's scheduled tasks. Data cached using any of the cache commands does not expire. It is cached *"forever"* as cache replacement strategy is relinquished to the developer and should be achieved through a well thought out scheduling of the cache commands.

For details, please refer to the [Task Scheduling](https://laravel.com/docs/9.x/scheduling#scheduling-artisan-commands) section of the Laravel documentation.

```php
$schedule->command('chimera:cache --data-source=enumeration')->everySixHours();
```

Basically, you add the above type of code to the schedule() method of your ```App\Console\Kernel``` class file for each of your cache commands.


```
php artisan chimera:cache-indicators
php artisan chimera:cache-scorecards
php artisan chimera:cache-mapindicators
php artisan chimera:cache-casestats
```

### chimera:cache-indicators

The command has three options which you can use to control how caching happens

- *max-level* : this option, when passed, will control the level depth of caching that will occur for indicators. By default, only national and first area levels will be cached. Accepts a number between 1 and the total number of area hierarchies you have

- *data-source* : this option can be used to update the cache of indicators that belong to that specific data source. By default, indicators across all data sources will be updated

- *tag* : this option, when passed, will specifically target indicators that have been assigned the given tag, excluding all other untagged indicators

Example: the first command would update all indicators (published and untagged), the second will update all indicators (published and untagged) within the enumeration questionnaire and the third one will update indicators that have the 'priority' tag

```
php artisan chimera:cache-indicators
php artisan chimera:cache-indicators --data-source=enumeration
php artisan chimera:cache-indicators --tag=priority
```

:::info
You can manage the tag list by editing the tags key under the cache chimera config

Example (in file config\chimera.php):
```php
'cache' => [
    'ttl' => env('CACHE_TTL_SECONDS', 60 * 30),
    'tags' => ['priority', 'secondary'],
],
```
then, when editing indicators you will see a dropdown named 'Cache Tags' which you can use to assign one of the tags you have set in the chimera config to each of your indicators. By default, indicators will have no assigned tag and you do not need to assign tags for indicators you 
do not want to target specifically. 
:::


### chimera:cache-scorecards

The command has two options which you can include to control how caching happens

- *data-source* : this option can be used to update the cache of scorecards that belong to that specific questionnaire. By default, scorecards across all questionnaires will be updated


### chimera:casestats

The command has one option which you can include to control how caching happens

- *data-source* : this option can be used to update the cache of CaseStats that belong to that specific questionnaire. By default, casestats across all questionnaires will be updated


## Cache clearing

If, for some reason, you need to clear cached data, you can use the ```chimera:cache-clear``` command. It has two options

- *data-source* : this option can be used to clear the cache of all items stored under the given questionnaire

- *type* : this option can be used to clear specific types of cached data. Possible values for this option are: *indicators, scorecards, casestats or mapindicators*

:::danger

Executing the cache-clear command without any options will clear the cache of everything! It will remove all entries from the cache. Consider this carefully before executing the command.
:::

## Cached data time stamp

When caching is enabled, a small, faded rubber stamp icon will appear somewhere over each indicator, scorecard and case stats table.

When hovered over, it will display the time the data being displayed was cached at.

![Cache time stamp display](/img/developer/advanced-topics/cache-timestamp-icon.png)