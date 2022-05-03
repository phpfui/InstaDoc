<?php

include './vendor/phpfui/phpfui/Installer.php';

$phpfuiInstaller = new \PHPFUI\Installer();

if (! $phpfuiInstaller->run($argv))
	{
	return;
	}

$vendor = [];
$vendor['scrivo'] = [
	'highlight.php/src/Highlight/styles' => 'highlighter',
	];

$vendor['phpfui'] = [
	'instadoc/css' => '',
	];

$phpfuiInstaller->copyFiles('../..', $vendor);

$css = [];
$css['instadoc/highlighter'] = [
	'styles' => 'highlighter',
	];

$phpfuiInstaller->copyFiles('..', $css);

