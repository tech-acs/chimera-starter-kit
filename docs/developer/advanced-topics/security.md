---
---

# Security

The Dashboard-Starter-Kit comes with an enhanced security system baked-in. Most of the typical security vulnerabilities are automatically protected against.

- Make sure you do not leave your server mis-configured. Environment should always be set to production and app debug should always be false.

- Make sure to set strong passwords. Use password generators to create cryptographically strong and random passwords.

The kit has a built-in check to warn the dashboard administrator that it is in an insecure mode.

![Developer mode warning](/img/developer/advanced-topics/warning.png)

If you see this warning icon blinking on the right side of the top nav bar, you should disable developer mode by making sure that the APP_ENV and APP_DEBUG variables are set to their secure settings which should be as follows

- APP_ENV=production
- APP_DEBUG=false




