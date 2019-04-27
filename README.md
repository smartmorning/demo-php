SmartMorning Demo integration in PHP
===========================
Concept
---------
    +--------------+              +----------------+             +-----------------------+
    |              |              |                |             |                       |
    | SmartMorning +------------> |   Web Server   | <-----------+    Home Automation    |
    | Android  App |              | with index.php |             | platform or appliance |
    |              |              |                |             |                       |
    +--------------+              +----------------+             +-----------------------+


The SmartMorning App will push all changes made to the Android Alarm Clock to the index.php script on your web server. The Home Automation platform can poll the index.php script for the timestamp of upcoming alarms.

Usage
------- 
Put the index.php script on a web server with PHP-support. Next to the index.php, a file smartmorning.json should be writable so it can store its state. 

If you put it on a web server configured for https://mywebsite.com in a directory smartmorning, you can call it as shown below. 
### Setter, used as url by the app
    https://mywebsite.com/smartmorning/action=set&person=john

Use the above url as configuration in the app, update the person-parameter with the owner of the Android device. The app will automaticly append the hour, minute, day, month and year parameter set the the timestamp of the next alarm.

### Get all upcoming alarms:
    https://mywebsite.com/smartmorning/action=get

Returns a json-string with all upcoming alarms sorted by time.

### Get the first upcoming alarm:
    https://mywebsite.com/smartmorning/action=next

Returns a json-string with the first upcoming alarm. Poll this url and use the alarm_delta_in_minutes to launch actions x time before the alarm clock will ring or y time after the alarm clock has rang.

