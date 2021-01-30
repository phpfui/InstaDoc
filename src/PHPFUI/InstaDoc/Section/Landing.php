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
			$accordion->addAttribute('data-allow-all-closed', 'true');
			$container->add(new \PHPFUI\SubHeader('Package Documentation'));

			foreach ($files as $file)
				{
				$parts = \explode('/', \str_replace('\\', '/', $file));
				$section = \array_pop($parts);
				$section = \str_replace('_', ' ', \strtolower($section));
				$section = \str_replace('.md', '', $section);
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
				$ul->add(new \PHPFUI\DescriptionDetail(new \PHPFUI\Link($this->controller->getNamespaceURL($namespace), $namespace, false)));
				}
			}

		$classNames = $node->getClassFilenames();

		if ($classNames)
			{
			$ul->add(new \PHPFUI\DescriptionTitle('Classes'));

			foreach ($node->getClassFilenames() as $class => $fullPath)
				{
				$ul->add(new \PHPFUI\DescriptionDetail(new \PHPFUI\Link($this->controller->getClassUrl($class), $class, false)));
				}
			}

		$container->add($ul);

		return $container;
		}
	}
