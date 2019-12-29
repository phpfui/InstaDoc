# PHPFUI\InstaDoc Library [![Build Status](https://travis-ci.org/phpfui/InstaDoc.png?branch=master)](https://travis-ci.org/phpfui/InstaDoc)

## A quick and easy way to add documention to your project

We all document our code with PHP [DocBlocks](https://en.wikipedia.org/wiki/PHPDoc) but we never seem to actually generate the documentation and add it to our project. Why? It simply takes too much time (over a minute), so we put it off till later, and later never comes.

But with PHPFUI\InstaDoc, you can document your site in about a minute (OK, maybe 5). The steps involved:
* Install PHPFUI\InstaDoc via Composer
* Run installation script
* Create document page

### Install PHPFUI\InstaDoc
composer require phpfui/InstaDoc

### Run Installation Script
Once installed, you need to run an installation script to copy static files to to your public directory.  From the project root, run the following:

php vendor/phpfui/InstaDoc/install.php yourPublicDirectory/subDirectory

Example: php vendor/phpfui/InstaDoc/install.php public/PHPFUI will add all needed files to public/PHPFUI, which will avoid any conflicts with your current files.  You can specify any directory, but PHPFUI is recomended to keep things simple.

### Create Document Page
PHPFUI\InstaDoc does not reply on any framework and can run on a standalone page. It is recommended that you do not make your documentation public, as PHPFUI\InstaDoc will display PHP source files. How you restrict access to the page is up to you.  The following does not restrict access and is simply an example:

```php
<?php
include 'yourAutoLoader.php';
// pass the directory containing your composer.json file
$fileManager = new \PHPFUI\InstaDoc\FileManager('../');
$fileManager->load();
$controller = new \PHPFUI\InstaDoc\Controller($fileManager);
echo $controller->display();
```
That is it. You are done!
