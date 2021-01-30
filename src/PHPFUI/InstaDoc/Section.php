<?php

namespace PHPFUI\InstaDoc;

/**
 * A generic Section with some base functionality
 *
 * Override methods to change layout
 */
class Section
	{
	protected $controller;

	public function __construct(Controller $controller)
		{
		$this->controller = $controller;
		}

	public function generate(\PHPFUI\InstaDoc\PageInterface $page, string $object) : \PHPFUI\Container
		{
		return new \PHPFUI\Container();
		}

	public function getBreadCrumbs(string $object) : \PHPFUI\BreadCrumbs
		{
		$breadCrumbs = new \PHPFUI\BreadCrumbs();
		$append = $namespace = '';

		foreach (\explode('\\', $object) as $part)
			{
			$namespace = $namespace . $append . $part;
			$breadCrumbs->addCrumb($part, $this->controller->getLandingPageUrl($namespace));
			$append = '\\';
			}

		return $breadCrumbs;
		}

	public function getClassBase(string $fullClassName) : string
		{
		$parts = \explode('\\', $fullClassName);

		return \array_pop($parts);
		}

	public function getMenu(string $className, array $allowedMenus) : ?\PHPFUI\Menu
		{
		$menu = new \PHPFUI\Menu();

		$currentPage = $this->controller->getParameter(Controller::PAGE, Controller::DOC_PAGE);
		$parts = $this->controller->getClassParts($className);

		foreach ($parts as $key => $value)
			{
			$this->controller->setParameter($key, $value);
			}

		if (\in_array(Controller::DOC_PAGE, $allowedMenus))
			{
			$docItem = new \PHPFUI\MenuItem('Docs', $this->controller->getPageUrl(Controller::DOC_PAGE));
			$docItem->setActive(Controller::DOC_PAGE == $currentPage);
			$menu->addMenuItem($docItem);
			}

		if (\in_array(Controller::FILE_PAGE, $allowedMenus))
			{
			$fileItem = new \PHPFUI\MenuItem('Source', $this->controller->getPageUrl(Controller::FILE_PAGE));
			$fileItem->setActive(Controller::FILE_PAGE == $currentPage);
			$menu->addMenuItem($fileItem);
			}

		if (\in_array(Controller::GIT_PAGE, $allowedMenus))
			{
			$node = \PHPFUI\InstaDoc\NamespaceTree::findNamespace($parts[Controller::NAMESPACE]);

			if ($node->getGit())
				{
				$gitItem = new \PHPFUI\MenuItem('Git', $this->controller->getPageUrl(Controller::GIT_PAGE));
				$gitItem->setActive(Controller::GIT_PAGE == $currentPage);
				$menu->addMenuItem($gitItem);
				}
			}

		// only show the menu if more than one
		if (\count($menu) > 1)
			{
			return $menu;
			}

		return null;
		}

	public function getNamespaceFromClass(string $class) : string
		{
		$parts = \explode('\\', $class);
		\array_pop($parts);

		return \implode('\\', $parts);
		}
	}
