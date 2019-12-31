<?php

include 'vendor/phpfui/Installer.php';

$phpfuiInstaller = new \PHPFUI\Installer()

if (! $installer->run($argv))
	{
	return;
	}


