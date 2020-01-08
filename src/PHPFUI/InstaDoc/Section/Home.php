<?php

namespace PHPFUI\InstaDoc\Section;

class Home extends \PHPFUI\InstaDoc\Section
	{

	public function generate(\PHPFUI\Page $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$parsedown = new \Parsedown();

		foreach ($this->controller->getHomePageMarkdown() as $file)
			{
			$md = file_get_contents($file);
			$container->add($parsedown->text($md));
			}
		$accordion = new \PHPFUI\Accordion();
		$accordion->addAttribute('data-allow-all-closed', 'true');
		$container->add(new \PHPFUI\SubHeader('Package Documentation'));

		$namespace = $this->getNamespaceFromClass($fullClassPath);

		$node = \PHPFUI\InstaDoc\NamespaceTree::findNamespace($namespace);

		foreach ($node->getMDFiles() as $file)
			{
			if (stripos($file, 'readme.md'))
				{
				$file = str_replace('\\', '/', $file);
				$md = file_get_contents($file);
				$parts = explode('/', $file);
				$package = $parts[count($parts) - 2];
				if ($namespace == '\\')
					{
					$namespace = '';
					}
				$accordion->addTab($namespace . '\\' . $package . ' Readme', $parsedown->text($md));
				}
			}
		$container->add($accordion);

		return $container;
		}
	}
