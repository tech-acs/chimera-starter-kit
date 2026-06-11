---
---

# Running queue workers

On the production server, you will need to run multiple queue workers which are needed to process the various queued jobs that the dashboard will generate.

For this, we use Laravel Horizon.

The kit contains a linux service template files which you can readily use to install the horizon service.

You can locate the file in the deploy directory. It is named horizon.service