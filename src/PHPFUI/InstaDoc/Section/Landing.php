<?php

namespace PHPFUI\InstaDoc\Section;

class Landing extends \PHPFUI\InstaDoc\Section
	{

	public function generate(\PHPFUI\Page $page, string $namespace) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add($this->getBreadCrumbs($namespace));

		$node = \PHPFUI\InstaDoc\NamespaceTree::findNamespace($namespace);
		$ul = new \PHPFUI\UnorderedList();

		foreach ($node->getClassFilenames() as $class => $fullPath)
			{
			$ul->addItem(new \PHPFUI\ListItem(new \PHPFUI\Link($this->controller->getClassURL($class), $class, false)));
			}
		$container->add($ul);

		$parsedown = new \Parsedown();
		$node = \PHPFUI\InstaDoc\NamespaceTree::findNamespace($namespace);
		$files = $node->getMDFiles();

		if (count($files))
			{
			$accordion = new \PHPFUI\Accordion();
			$accordion->addAttribute('data-allow-all-closed', 'true');
			$container->add(new \PHPFUI\SubHeader('Package Documentation'));
			foreach ($files as $file)
				{
				$parts = explode('/', str_replace('\\', '/', $file));
				$section = array_pop($parts);
				$section = str_replace('_', ' ', strtolower($section));
				$section = str_replace('.md', '', $section);
				$md = file_get_contents($file);
				$accordion->addTab(ucwords($section), $parsedown->text($md));
				}
			$container->add($accordion);
			}

		return $container;
		}
	}
