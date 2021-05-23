<?php

namespace PHPFUI\InstaDoc\Section;

class Home extends \PHPFUI\InstaDoc\Section
	{
	public function generate(\PHPFUI\InstaDoc\PageInterface $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$parsedown = new \PHPFUI\InstaDoc\MarkDownParser($page);

		$hr = '';

		foreach ($this->controller->getHomePageMarkdown() as $file)
			{
			$container->add($hr);
			$hr = '<hr>';
			$container->add($parsedown->fileText($file));
			}
		$accordion = new \PHPFUI\Accordion();
		$accordion->addAttribute('data-allow-all-closed', 'true');
		$container->add(new \PHPFUI\SubHeader('Package Documentation'));

		foreach (\PHPFUI\InstaDoc\NamespaceTree::getAllMDFiles() as $file)
			{
			if (\stripos($file, 'readme.md'))
				{
				$file = \str_replace('\\', '/', $file);
				$parts = \explode('/', \str_replace('.', '', $file));
				\array_pop($parts);
				$accordion->addTab(\implode('\\', $parts) . ' Readme', $parsedown->fileText($file));
				}
			}
		$container->add($accordion);

		return $container;
		}
	}
