<?php

include './vendor/phpfui/phpfui/Installer.php';

$phpfuiInstaller = new \PHPFUI\Installer();

if (! $phpfuiInstaller->run($argv))
	{
	return;
	}

$vendor = [];
$vendor['scrivo'] = [
	'highlight.php/styles' => 'highlighter',
	];

$vendor['phpfui'] = [
	'instadoc/css' => '',
	];

$phpfuiInstaller->copyFiles('../..', $vendor);

$css = [];
$css['instadoc/highlighter/styles'] = [
	'PHP.css' => 'highlighter/styles',
	'PHPFUI.css' => 'highlighter/styles',
	];

$phpfuiInstaller->copyFiles('..', $css);

