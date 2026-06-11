---
---

# Creating gauges
Gauges are meant to provide a clear visualization of a single metric relative to a predefined goal or maximum value. Unlike standard bar charts, Gauges focus on proportionality and achievement. In addition to the numeric representation of achievement, color scales are used to obviate the score without much cognitive load. 

There are two ways to create gauges. A cli command and a web form.

The first way is by running the `php artisan chimera:make-gauge` command and following the various prompts. This works best when you are running a linux machine.

The second way is by going to the Manage dashboard menu and selecting Gauges, then pressing the CREATE NEW button and filling out the form as required.

Gauges usually display just three things: title, sub-title and value (with unit or reference).

![Gauges](/img/developer/building-your-dashboard/gauges.png)

## Implementing gauges
Obviously, you will have to write some code in your generated gauge file so that it distills and returns the values you intend.

You have a high degree of freedom on how you choose to code your gauge as long as, at the end, you set the appropriate public class properties with their desired values. You have to return a Laravel Collection from the getData public method containing an object with a key called 'value'. This will be the value to display. You should also make sure the $unit, $outOf and $colorThresholds properties are set. The generated stub file will have all of these included.


- $this->outOf

    This is the mathematical denominator. It defines the "perfect score" or the target/maximum value for the gauge.

- $this->colorThresholds

    This is the semantic styling engine. It maps numerical values to CSS classes (Tailwind colors) to provide immediate "good/bad" status.

- $this->unit

    This is the display suffix. While $outOf handles the math, $unit handles the visual text rendered in the center of the gauge.