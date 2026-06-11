---
---

# Configuration

## Environment variables
There are a few environment variables that you can set/change to control various aspects of the dashboard.

### Database and queue
Primarily, you will already have set the database connection variables
- DB_CONNECTION=pgsql
- DB_HOST=your database host name or ip address
- DB_PORT=your database port (5432 is the default)
- DB_DATABASE=your database name
- DB_USERNAME=your database username
- DB_PASSWORD=your database password

In addition, you should also set the following variables

- APP_NAME="Your preferred dashboard name/title, shown in the title bar"
- QUEUE_CONNECTION=redis

### Mail
If you intend to send emails for registration invites, notifications, password recovery, etc, then you need to enable mail and also configure the mail server details.

This is configured on the "Settings" page under the "Mail settings" section. If mail is enabled, the dashboard will attempt to send all emails via the SMTP server you have configured. Otherwise, the MAIL_MAILER setting in your .env file will be used.

Please make sure the SMTP server details you input are correct and tested to avoid errors in the dashboard operation.

### Dashboard features
The following are other environment variables you can set to affect various aspects of the dashboard. Some of these are easily set from the "Settings" management menu on the web UI and the rest need to be set directly in the .env file

<b>From settings page</b>
- APP_OWNER_NAME=ECA

  set this to the organization that owns the dashboard. Used in the footer displayed across all pages (default ECA)

- APP_OWNER_URL=#

  set this to the URL (website) of the organization that owns the dashboard. Used in the footer displayed across all pages (default #)

- INDICATORS_PER_PAGE=2

    set this to an even integer number which controls the number of indicators shown per page (default 2)

- RECORDS_PER_PAGE=20
    
    set this to an integer number which controls the number of rows shown in various tables of the dashboard (default 20)

- MAP_CENTER_LAT=9.005401
    
    set this to the latitude of the map which is first panned into view when map is loaded (default 9.005401)

- MAP_CENTER_LON=38.763611
    
    set this to the longitude of the map which is first panned into view when map is loaded (default 38.763611)

- MAP_STARTING_ZOOM=7

    set this to the desired starting map zoom level. When navigating to the map page, this will be starting zoom level (default 7)

- FEATURED_INDICATORS_PER_DATA_SOURCE=2

    on the home page, for each of the data sources, you can select and feature a number of indicators. You can set how many using this variable (default 2)

- MAIL_ENABLED

    turn this on if you intend to send emails through the system (default false) and configure all the SMTP details
  
<b>From .env file</b>
- CACHE_TTL_SECONDS=1800

  set this to the number of seconds that you want database query results to be cached (default 60 * 30; thirty minutes)
 
- APP_TIMEZONE=UTC

    set this to the timezone of where the census/survey exercise is taking place (default UTC). You should only use valid timezones as per the php docs [here](https://www.php.net/manual/en/timezones.php)

- SECURE=false
    
    set this to true or false depending on whether you have https enabled on your dashboard web server (default false)

- ENFORCE_2FA=false
    
    set this to true to require users to enable and use two-factor authentication (default false) 

- INVITATION_TTL_HOURS=72
    
    set this to the number of hours you want user registration links to be valid for (default 72)

- REQUIRE_ACCOUNT_APPROVAL=false

    set this to true to require that all accounts get approval from dashboard manager before they can be used (default false)

- LONG_QUERY_TIME=10

  the app will time and record how long each query of the dashboard artefacts takes above the set minimum (default 10 seconds)