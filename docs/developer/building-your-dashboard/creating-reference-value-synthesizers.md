---
---

# Creating reference value synthesizers

Reference value synthesizers are classes that are meant to generate reference values from an existing data source. E.g. Generating total number of households per area from a listing exercise to serve as reference values for the respective indicators in the enumeration exercise.

To create a reference value synthesizer, you can use the `php artisan chimera:make-reference-value-synthesizer` command and following the various prompts.
Once the class file is created (located in `app/ReferenceValueSynthesizers`), you will need to implement the `getData` method.

The returned collection must have at least 'area_path' and 'value' keys. Using BreakoutQueryBuilder with the lastlyAreaRightJoinData() call will include area_path column.

## Using reference value synthesizers
Once you have implemented your various reference value synthesizers, you can use them to generate and write reference values to the database. You can do this by running the `php artisan chimera:transfer-reference-values ClassName` command, where ClassName is the name of the reference value synthesizer class.
This will generate reference values for the respective indicators in the enumeration exercise. If the reference values are generated for EAs, then the synthesizer will make sure to generate them for the higher level areas (as per their additivity or singularity parameter that was set during the generation of the class)

E.g.
`php artisan chimera:transfer-reference-values NoOfHouseholdsReferenceValue`

## Generating reference values on a regular interval
Ideally, you would want to generate reference values for a proceeding exercise only once the previous exercise is completed. However, in reality, the two exercises might overlap and the reference values might need to get updated on a regular basis. To do this, you can schedule the generation of reference values using the scheduler. 

You may use the `withSchedule` method in your application's `bootstrap/app.php` file to define your scheduled tasks. This method accepts a closure that receives an instance of the scheduler:

```php
->withSchedule(function (Schedule $schedule) {
    $schedule->command('chimera:transfer-reference-values NoOfHouseholdsReferenceValue')
        ->daily()
        ->at('00:00');
})
```
Remember to import the Schedule class at the top of the file, like so:
`use Illuminate\Console\Scheduling\Schedule;`

:::caution
When generating reference values, if a reference value already exists for a given **area and indicator** pair, then its value will be overwritten (updated).
:::