# README
By Mats Ljungquist

## What?
DisImg is a picture album for close friends and relatives. The owner of the album uploads pictures
and creates users/viewers for the album. The owner decides which pictures a viewer can see.
Viewers can mark an interest in a picture (or quite possible the content...) and then the owner
can view these markings. So the picture album can be used as a means to give away stuff
no longer needed to friends and relatives.

## Features
 * Captcha - securimage - this feature can be turned on/off in the config-file
 * Logging - homemade - this feature can be turened on/of in the config-file
 * jQuery plugins - form.plugin
 * jQuery UI - for dialogs and some other stuff
 * Thumbnails - support for creating of thumbnails through 'EasyPhpThumbnail'
 * jGrowl - for information messages
 * tinyeditor - for wysiwyg editing

## Download and install
 
DisImg is on GitHub.
 
[http://github.com/matslj/disimg/](http://github.com/matslj/disimg/)
 
Download it either by 'git clone' or as a tag-zip (preferably the latest tag) and
then change the following define in 'config.php':

- define('WS_SITELINK',   'http://<your domain + path to where index.php is located>/'); // Link to site.

So if you for example have your index.php in localhost/disimg/ then the above define should read:

define('WS_SITELINK',   'http://localhost/disimg/'); // Link to site. (Observe that the sitlink MUST end with a slash)

That was the installation of the code. Now the database has to be configured. To do this
edit the file <your install directory>/sql/config.php with information about your database. Then,
back in the browser, point your browser to:

the location of WS_SITELINK followd by ?p=install, for example http://localhost/disimg/?p=install

and then follow the instructions to in order to set up all the necessary tables and stored routines
required by disimg (press 'Destroy current database and create from scratch'). Some sample data
will also be installed.

Also there are two directorys that may need to be added manually to the root of your installation. These are:
 * log - log files will end up here. Make sure that the file permissions allow write. Toggle logging on off in config.php.
 * uploads - the uploaded files (and created thumbnails) will end up here. Make sure the the file permissions allow write.

You are also required to enter the system file path to the location of your uploads directory in 
the config.php file (FILE_ARCHIVE_PATH). This could for example be: /var/www/disimg/. See config.php for further explanation.

Now the complete power of DisImg is at your hands.

In final deployment, the install directory (<your install directory>/pages/install) should be erased.
 
An example of a pure standard installation of DisImg is available here (not available yet). Review it before moving
on.
 
5. DisImg, The license
 
Free software. No warranty.
 
 .
..: &copy; Mats Ljungquist, 2012