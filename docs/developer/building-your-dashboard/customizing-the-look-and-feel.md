---
---

# Customizing the look and feel

## Changing the logo

There are two different resources that control the logo graphics used in the dashboard.

One for the login page (<span className='text--success text--light'>resources/views/vendor/jetstream/components/authentication-card-logo.blade.php</span>) and another for everywhere else (<span className='text--success text--light'>resources/views/vendor/jetstream/components/application-mark.blade.php</span>)

By changing the contents of these two files, you can change the logo graphics. Both these resources are of SVG code and we advice that you replace them with either the SVG code of your logo or an SVG file format of your logo.

To change the hero image on the landing page (welcome page), just replace it (<span className='text--success text--light'>public/images/hero.jpg</span>) with a file of the same name.

You are also able to control the color of charts, scorecards and other graphics in the dashboard by creating your own theme. Themes are detailed under the **Advanced topics** section.