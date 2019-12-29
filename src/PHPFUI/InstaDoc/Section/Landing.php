<?php

namespace PHPFUI\InstaDoc\Section;

class Landing extends \PHPFUI\InstaDoc\Section
	{

	public function generate(\PHPFUI\Page $page, string $namespace) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add($this->getBreadCrumbs($namespace));

		$namespaceTree = \PHPFUI\InstaDoc\NamespaceTree::getNamespaceTree($namespace . '\\Class');
		$ul = new \PHPFUI\UnorderedList();

		foreach ($namespaceTree->getClasses() as $class => $fullPath)
			{
			$ul->addItem(new \PHPFUI\ListItem(new \PHPFUI\Link($this->controller->getClassURL($namespace, $class), $class, false)));
			}
		$container->add($ul);

		$parsedown = new \Parsedown();
		$files = $this->controller->getFileManager()->getFilesInRepository($namespace, '.md');
		$hr = '';

		foreach ($files as $file)
			{
			if (stripos($file, 'readme.md'))
				{
				$container->add($hr);
				$hr = '<hr>';
				$md = file_get_contents($file);
				$container->add($parsedown->text($md));
				}
			}

		return $container;
		}
	}
