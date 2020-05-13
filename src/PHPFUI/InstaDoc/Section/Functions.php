<?php

namespace PHPFUI\InstaDoc\Section;

class Functions extends \PHPFUI\InstaDoc\Section\CodeCommon
	{

	public function __construct(\PHPFUI\InstaDoc\Controller $controller)
		{
		parent::__construct($controller);
		}

	public function generate(\PHPFUI\Instadoc\PageInterface $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		// include the file to make sure the function are loaded
		require_once $fullClassPath;

		// parse out the function names
		$functions = [];
		$file = file_get_contents($fullClassPath);
		$file = str_replace("\n", ' ', $file);
		$index = 0;
		$needle = 'function ';

		while ($index = strpos($file, $needle, $index))
			{
			// find next function
			$index += strlen($needle);
			$end = strpos($file, '(', $index);
			$functions[] = trim(substr($file, $index, $end - $index));
			}

		$namespace = '';
		$needle = 'namespace ';
		$index = strpos($file, $needle);

		if ($index)
			{
			$index += strlen($needle);
			$end = strpos($file, ';', $index);
			$namespace = trim(substr($file, $index, $end - $index));
			}

		foreach ($functions as $function)
			{
			$container->add($this->documentFunction($namespace . '\\' . $function));
			}

		return $container;
		}

	private function documentFunction(string $function) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();
		$container->add('<hr>');

		try
			{
			$this->reflection = new \ReflectionFunction($function);
			}
		catch (\throwable $e)
			{
			$container->add(new \PHPFUI\Header($e->getMessage(), 5));

			return $container;
			}

		$properties = [
			'Closure',
			'Deprecated',
			'Generator',
			'Internal',
			'Variadic',
			];

		$gridX = null;

		foreach ($properties as $type)
			{
			$isType = 'is' . $type;

			if ($this->reflection->{$isType}())
				{
				if (! $gridX)
					{
					$gridX = new \PHPFUI\GridX();
					}
				$gridX->add($this->section($type));
				}
			}
		$container->add($gridX);

		$container->add($this->getColor('keyword', 'function'));

		if ($this->reflection->returnsReference())
			{
			$container->add($this->getColor('keyword', '&'));
			}

		$namespace = $this->reflection->getNamespaceName();

		if ($namespace)
			{
			$container->add(' ');
			$container->add(new \PHPFUI\Link($this->controller->getLandingPageUrl($namespace), $namespace, false));
			$container->add('\\');
			}
		$container->add($this->getColor('name', $this->reflection->getShortName()));
		$container->add($this->getParameters($this->reflection));

		return $container;
		}

	}
