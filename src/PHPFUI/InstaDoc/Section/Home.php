<?php

namespace PHPFUI\InstaDoc\Section;

class Home extends \PHPFUI\InstaDoc\Section
	{
	public function generate(\PHPFUI\InstaDoc\PageInterface $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$parsedown = new \PHPFUI\InstaDoc\MarkDownParser();

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
			$parts = \explode('/', \str_replace('\\', '/', $file));
			// remove the first part, which is ../
			\array_shift($parts);
			// $section is the file name
			$section = \array_pop($parts);
			// remove .md
			$section = \substr($section, 0, \strlen($section) - 3);
			// make more readable
			$section = \str_replace('_', ' ', \ucwords(\strtolower($section)));
			// proper case words
			$accordion->addTab(\implode('\\', $parts) . ' ' . $section, $parsedown->fileText($file));
			}
		$container->add($accordion);

		return $container;
		}
	}
