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

		$files = array_keys(\PHPFUI\InstaDoc\NamespaceTree::getAllMDFiles());

		foreach ($files as $file)
			{
			if (stripos($file, 'readme.md'))
				{
				$file = str_replace('\\', '/', $file);
				$md = file_get_contents($file);
				$parts = explode('/', str_replace('.', '', $file));
				array_pop($parts);
				$accordion->addTab(implode('\\', $parts). ' Readme', $parsedown->text($md));
				}
			}
		$container->add($accordion);

		return $container;
		}
	}
