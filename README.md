# README
By Mats Ljungquist

## What?
DisImg is a picture album for close friends and relatives. The owner of the album uploads pictures
and creates users/viewers for the album. The owner decides which pictures a viewer can see.
Viewers can mark an interest in a picture (or quite possible the content...) and then owner
can view these markings. So the picture album can be used as a means to give away stuff
no longer needed to friends and relatives.

## Features
 * Captcha - securimage
 * jQuery plugins - form.plugin
 * jQuery UI - for dialogs and some other stuff

## Download and install
 
DisImg is on GitHub.
 
[http://github.com/matslj/disimg/](http://github.com/matslj/disimg/)
 
Download it either by 'git clone' or as a tag-zip (preferably the latest tag) and
then change the following define in 'config.php':

- define('WS_SITELINK',   'http://<your domain + path to where index.php is located>/'); // Link to site.

So if you for example have your index.php in localhost/munchy/ then the above define should read:

define('WS_SITELINK',   'http://localhost/disimg/'); // Link to site.

Then point a browser to your installed version of DisImg. Voila! Done.

But to get all the facilities of DisImg up and running, you also have to config the database. To do this
edit the file <your install directory>/sql/config.php with information about your database. Then,
back in the browser, pointing at your installed version of DisImg, click 'Install' and then
'Destroy current database and create from scratch'. This creates the necessary tables and stored routines as well
as populating the tables with some example data.

Now the complete power of DisImg is at your hands.
 
An example of a pure standard installation of DisImg is available here. Review it before moving
on.
 
5. DisImg, The license
 
Free software. No warranty.
 
 .
..: &copy; Mats Ljungquist, 2012