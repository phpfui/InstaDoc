<?php

namespace PHPFUI\InstaDoc\Section;

class Home extends \PHPFUI\InstaDoc\Section
	{

	public function generate(\PHPFUI\Page $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$parsedown = new \Parsedown();

		$hr = '';

		foreach ($this->controller->getHomePageMarkdown() as $file)
			{
			$md = file_get_contents($file);
			$container->add($hr);
			$hr = '<hr>';
			$container->add($parsedown->text($md));
			}
		$accordion = new \PHPFUI\Accordion();
		$accordion->addAttribute('data-allow-all-closed', 'true');
		$container->add(new \PHPFUI\SubHeader('Package Documentation'));

		foreach (\PHPFUI\InstaDoc\NamespaceTree::getAllMDFiles() as $file)
			{
			if (stripos($file, 'readme.md'))
				{
				$file = str_replace('\\', '/', $file);
				$md = file_get_contents($file);
				$parts = explode('/', str_replace('.', '', $file));
				array_pop($parts);
				$accordion->addTab(implode('\\', $parts) . ' Readme', $parsedown->text($md));
				}
			}
		$container->add($accordion);

		return $container;
		}
	}
