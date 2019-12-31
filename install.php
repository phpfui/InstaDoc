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

