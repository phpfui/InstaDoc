# PHPFUI\InstaDoc Library [![Build Status](https://travis-ci.org/phpfui/InstaDoc.png?branch=master)](https://travis-ci.org/phpfui/InstaDoc)

## A quick and easy way to add documention to your project

We all document our code with PHP [DocBlocks](https://en.wikipedia.org/wiki/PHPDoc) but we never seem to actually generate the documentation and add it to our project. Why? It simply takes too much time (over a minute), so we put it off till later, and later never comes.

But with PHPFUI\InstaDoc, you can document your site in about a minute (OK, maybe 2). The steps involved:
 * Install PHPFUI\InstaDoc via Composer (30 seconds)
 * Run installation script (30 seconds)
 * Create document page (1 minute, 5 lines of code)

 Two minutes to usable documentation with the following features:

## PHPFUI\InstaDoc Features
 * Always up to date, even with code that is not yet checked in
 * Documents all projects loaded via Composer automatically
 * Add any local repo directories
 * Remove any Composer project you don't care about
 * Tabbed documentation so you are not looking at irrelevant methods
 * Alphabetized everything, no more searching unalphabetized pages!
 * Support for markdown and custom markdown pages
 * Quick access to highlighed PHP source with user selectable highlighting
 * Quick access to the file's git history for the local repo
 * Ability to generate static html files for high volume sites
 * 5+ line config compatible with all PHP frameworks, or standalone
 * Completely configureable UI if you don't like the default
 * Uses [Foundation CSS framework](https://get.foundation) for a great experience on mobile

### Install PHPFUI\InstaDoc
composer require phpfui/InstaDoc

### Run Installation Script
Once installed, you need to run an installation script to copy static files to to your public directory.  From the project root, run the following:

php vendor/phpfui/instadoc/install.php yourPublicDirectory/subDirectory

Example: php vendor/phpfui/instadoc/install.php public/PHPFUI will add all needed files to public/PHPFUI, which will avoid any conflicts with your current files.  You can specify any directory by using \PHPFUI\Page::setResourcePath, but PHPFUI is recomended to keep things simple.

### Create Document Page
PHPFUI\InstaDoc does not reply on any framework and can run on a standalone page. It is recommended that you do not make your documentation public, as PHPFUI\InstaDoc will display PHP source files. How you restrict access to the page is up to you.  The following does not restrict access and is simply an example:

```php
<?php
include 'yourAutoLoader.php';

// pass the directory containing your composer.json file
$fileManager = new \PHPFUI\InstaDoc\FileManager('../');

// add you App class tree in
$fileManager->addNamespace('App', '../App', true);

// load you cached files
$fileManager->load();

// get the controller
$controller = new \PHPFUI\InstaDoc\Controller($fileManager);

// display will return a fully formed page
echo $controller->display();
```
That is it. You are done!

### Example and Full Documentation

[PHPFUI/InstaDoc](http://www.phpfui.com)
