<?php

namespace PHPFUI\InstaDoc\Section;

class Landing extends \PHPFUI\InstaDoc\Section
	{
	public function generate(\PHPFUI\InstaDoc\PageInterface $page, string $namespace) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add($this->getBreadCrumbs($namespace));

		$parsedown = new \PHPFUI\InstaDoc\MarkDownParser();
		$node = \PHPFUI\InstaDoc\NamespaceTree::findNamespace($namespace);
		$files = $node->getMDFiles();

		if (\count($files))
			{
			$accordion = new \PHPFUI\Accordion();
			$accordion->addAttribute('data-multi-expand', 'true');
			$accordion->addAttribute('data-allow-all-closed', 'true');
			$container->add(new \PHPFUI\SubHeader('Package Documentation'));

			foreach ($files as $file)
				{
				$parts = \explode('/', \str_replace('\\', '/', $file));
				// $section is the file name
				$section = \array_pop($parts);
				// remove .md
				$section = \substr($section, 0, \strlen($section) - 3);
				// make more readable and proper case words
				$section = \str_replace('_', ' ', \strtolower($section));
				$accordion->addTab(\ucwords($section), $parsedown->fileText($file));
				}
			$container->add($accordion);
			}

		$node = \PHPFUI\InstaDoc\NamespaceTree::findNamespace($namespace);
		$ul = new \PHPFUI\DescriptionList();

		$children = $node->getChildren();

		if ($children)
			{
			$ul->add(new \PHPFUI\DescriptionTitle('Namespaces'));

			foreach ($children as $child)
				{
				$namespace = $child->getNamespace();
				$ul->add(new \PHPFUI\DescriptionDetail(new \PHPFUI\Link($this->controller->getNamespaceURL($namespace), \str_replace('\\', '<wbr>\\', $namespace), false)));
				}
			}

		$classNames = $node->getClassFilenames();

		if ($classNames)
			{
			$ul->add(new \PHPFUI\DescriptionTitle('Classes'));

			foreach ($node->getClassFilenames() as $class => $fullPath)
				{
				$ul->add(new \PHPFUI\DescriptionDetail(new \PHPFUI\Link($this->controller->getClassUrl($class), \str_replace('\\', '<wbr>\\', $class), false)));
				}
			}

		$container->add($ul);

		return $container;
		}
	}
