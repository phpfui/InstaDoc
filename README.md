# PHPFUI\InstaDoc Library [![Build Status](https://travis-ci.com/phpfui/InstaDoc.png?branch=master)](https://travis-ci.com/phpfui/InstaDoc) [![Latest Packagist release](https://img.shields.io/packagist/v/phpfui/InstaDoc.svg)](https://packagist.org/packages/phpfui/InstaDoc)

## A quick and easy way to add documentation to your PHP project

We all document our code with PHP [DocBlocks](https://en.wikipedia.org/wiki/PHPDoc) but we never seem to actually generate the documentation and add it to our project. Why? It simply takes too much time (over a minute), so we put it off till later, and later never comes.

But with PHPFUI\InstaDoc, you can document your site in about a minute (OK, maybe 2). The steps involved:
 * Install PHPFUI\InstaDoc via Composer (30 seconds)
 * Run installation script (30 seconds)
 * Create document page (1 minute, 6 lines of code)

 Two minutes to usable documentation with the following features:

## PHPFUI\InstaDoc Features
 * Always up to date, even with code that is not yet checked in
 * Send constructor information including parameters and default values to clipboard
 * Child and Parent class hierarchy clearly displayed and accessable
 * Quick access to highlighted PHP source with user selectable highlighting
 * Quick access to the file's git history for the local repo
 * Full support for @inheritDoc tag so child method docs are displayed correctly
 * Documents all projects loaded via Composer automatically
 * Tabbed documentation so you are not looking at irrelevant methods
 * Alphabetized everything, no more searching unalphabetized pages!
 * Support for markdown and custom markdown pages
 * Ability to generate static html files for high volume sites
 * Add any local repo directories
 * Remove any Composer project you don't care about
 * 5+ line config compatible with all PHP frameworks, or standalone
 * Uses [Foundation CSS framework](https://get.foundation) for a great experience on mobile

### Install PHPFUI\InstaDoc (requires PHP >= 7.3)
```
composer require phpfui/InstaDoc
```
### Run Installation Script
Once installed, you need to run an installation script to copy static files to your public directory.  From your project root, run the following:
```
php vendor/phpfui/instadoc/install.php yourPublicDirectory/subDirectory
```
Example: **php vendor/phpfui/instadoc/install.php public/PHPFUI** will add all needed files to public/PHPFUI, which will avoid any conflicts with your current files.  You can specify any directory by using \PHPFUI\Page::setResourcePath, but PHPFUI is recomended to keep things simple.

### Create Document Page
PHPFUI\InstaDoc does not reply on any framework and can run on a standalone page. It is recommended that you do not make your documentation public, as PHPFUI\InstaDoc will display PHP source files. How you restrict access to the page is up to you.  The following does not restrict access and is simply an example:

```php
<?php
include 'yourAutoLoader.php';

// pass the directory containing your composer.json file
$fileManager = new \PHPFUI\InstaDoc\FileManager('../');

// add your App class tree in, pass true as the last parameter if this namespace is in your local git repo.
$fileManager->addNamespace('App', '../App', true);

// load your cached files
$fileManager->load();

// load child classes if you want to display them, if you don't do this step, docs will not show classes that extend the displayed class
\PHPFUI\InstaDoc\ChildClasses::load();

// get the controller
$controller = new \PHPFUI\InstaDoc\Controller($fileManager);

// display will return a fully formed page
echo $controller->display();
```
That is it. You are done!

### Adding New Classes
PHPFUI\InstaDoc saves the classes to display in PHP serialized files.  Delete those files (.serial extension) when you want to display new classes. PHPFUI\InstaDoc will regenerate automatically if the files are missing.

### Removing a Namespace
```php
\PHPFUI\InstaDoc\NamespaceTree::deleteNameSpace('cebe\markdown\tests');
```

### Add git Repository Page
The git repo path defaults to the composer directory, but you can change the path by calling:
```php
$controller->setGitRoot(getcwd() . '/../');
```

### Add Documents To Your Docs Home Page
```php
$controller->addHomePageMarkdown('../PHPFUI/InstaDoc/README.md');
```

### Set Your Home Page
You may want users to get back into your system easily. Clicking on the top left menu bar will take them here:
```php
$controller->setHomeUrl('/');
```

### Breakup Your Documentation Into Sections
If you have a lot of source code, you might want to break it into sections, so you will need a separate file to store the index in per section:
```php
$fileManager->setBaseFile('SubProject');
```

### Generate Static Files
Just the doc and file pages, no git!
```php
$controller->generate('static/file/path', [\PHPFUI\InstaDoc\Controller::DOC_PAGE, \PHPFUI\InstaDoc\Controller::FILE_PAGE, ]));
```

### Example and Full Documentation

[PHPFUI/InstaDoc](http://www.phpfui.com)
