<?php
// allow the autoloader and db to be included from any script that needs it.
error_reporting(E_ALL);

function classNameExists(string $className) : string
	{
	$dir = (strpos($className, "\\") === false) ? '..\\NoNameSpace' : '..';
	$path = "{$_SERVER['DOCUMENT_ROOT']}\\{$dir}\\{$className}.php";
	if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
		$path = str_replace("\\", '/', $path);
		}
	return file_exists($path) ? $path : '';
	}

function autoload($className)
	{
	$path = classNameExists($className);
	if ($path)
		{
		/** @noinspection PhpIncludeInspection */
		include $path;
		}
	}

spl_autoload_register('autoload');

date_default_timezone_set('America/New_York');

