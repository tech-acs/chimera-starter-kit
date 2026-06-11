---
---

# Production checklist

The Dashboard Starter Kit comes with a production-checklist command that will run through a series of checks and provides you with a pass/fail result for each.

```
php artisan chimera:production-checklist
```

These checks cover important settings, configurations and more that are critical to the proper functioning of the dashboard in production environments.

![production-checklist](/img/developer/deployment-guide/production-checklist.png)

Obviously, if you have any failing checks, you should remedy them until you have all passing checks.