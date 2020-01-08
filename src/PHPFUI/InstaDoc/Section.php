<?php

namespace PHPFUI\InstaDoc;

class Section
	{

	protected $controller;

	public function __construct(Controller $controller)
		{
		$this->controller = $controller;
		}

	public function getClassBase(string $fullClassName) : string
		{
		$parts = explode('\\', $fullClassName);

		return array_pop($parts);
		}

	public function getNamespaceFromClass(string $class) : string
		{
		$parts = explode('\\', $class);
		array_pop($parts);
		return implode('\\', $parts);
		}

	public function generate(\PHPFUI\Page $page, string $object) : \PHPFUI\Container
		{
		return new \PHPFUI\Container();
		}

	public function getBreadCrumbs(string $object) : \PHPFUI\BreadCrumbs
		{
		$breadCrumbs = new \PHPFUI\BreadCrumbs();
		$append = $namespace = '';

		foreach (explode('\\', $object) as $part)
			{
			$namespace = $namespace . $append . $part;
			$breadCrumbs->addCrumb($part, $this->controller->getLandingPageUrl($namespace));
			$append = '\\';
			}

		return $breadCrumbs;
		}

	public function getMenu(string $className) : \PHPFUI\Menu
		{
		$menu = new \PHPFUI\Menu();

		$currentPage = $this->controller->getParameters()[Controller::PAGE];
		$parts = $this->controller->getClassParts($className);
		$this->controller->setParameters($parts);
		$docItem = new \PHPFUI\MenuItem('Docs', $this->controller->getPageUrl(Controller::DOC_PAGE));
		$docItem->setActive(Controller::DOC_PAGE == $currentPage);
		$menu->addMenuItem($docItem);
		$fileItem = new \PHPFUI\MenuItem('File', $this->controller->getPageUrl(Controller::FILE_PAGE));
		$fileItem->setActive(Controller::FILE_PAGE == $currentPage);
		$menu->addMenuItem($fileItem);
		$node = \PHPFUI\InstaDoc\NamespaceTree::findNamespace($parts[Controller::NAMESPACE]);
		if ($node->getGit())
			{
			$gitItem = new \PHPFUI\MenuItem('Git', $this->controller->getPageUrl(Controller::GIT_PAGE));
			$gitItem->setActive(Controller::GIT_PAGE == $currentPage);
			$menu->addMenuItem($gitItem);
			}

		return $menu;
		}


	}
