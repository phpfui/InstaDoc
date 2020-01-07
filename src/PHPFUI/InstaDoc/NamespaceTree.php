<?php

namespace PHPFUI\InstaDoc;

class NamespaceTree
	{
	private static $activeClass;
	private static $activeNamespace;
	private static $controller;
	private static $root = null;

	private $children = [];
	private $classes = [];
	private $namespace = '';
	private $parent = null;

	// only we can make us to ensure the tree is good
	private function __construct()
		{
		}

	/**
	 * Returns array of all classes
	 */
  public static function getAllClasses(?NamespaceTree $tree = null) : array
		{
		if (! $tree)
			{
			$tree = self::getRoot();
			// sort it to be sure
			self::sort($tree);
			}

		$classes = [];
		foreach ($tree->children as $child)
			{
			$classes = array_merge($classes, self::getAllClasses($child));
			}

		$namespace = $tree->getNamespace();

		foreach ($tree->classes as $class => $path)
			{
			$classes[$path] = $namespace . '\\' . $class;
			}

		return $classes;
		}

	/**
	 * Return all the child namespaces of the current node.
	 */
	public function getChildren() : array
		{
		return $this->children;
		}

	/**
	 * Return an array with full paths of all the classes in the
	 * namespace, indexed by class name
	 */
	public function getClasses() : array
		{
		return $this->classes;
		}

	/**
	 * Returns the full namespace all the way up to the root.
	 */
	public function getNamespace() : string
		{
		$namespace = $this->namespace;

		$tree = $this->parent;

		while ($tree && $namespace)
			{
			$namespace = $tree->namespace . '\\' . $namespace;
			$tree = $tree->parent;
			}

		return $namespace;
		}

	/**
	 * Given a class, return it full path.
	 *
	 * Returns the NamespaceTree node that contains the class. If
	 * you pass in a path, it sets the class's path.
	 */
	public static function getNamespaceTree(string $fullClassName, string $path = '') : NamespaceTree
		{
		$parts = explode('\\', str_replace('\\\\', '\\', $fullClassName));
		$rootNamespace = array_shift($parts);
		$className = array_pop($parts);

		if (! isset(self::getRoot()->children[$rootNamespace]))
			{
			$root = new NamespaceTree();
			$root->namespace = $rootNamespace;
			self::$root->children[$rootNamespace] = $root;
			}
		$parent = self::$root->children[$rootNamespace];

		foreach ($parts as $partialNamespace)
			{
			if (! isset($parent->children[$partialNamespace]))
				{
				$child = new NamespaceTree();
				$child->namespace = $partialNamespace;
				$child->parent = $parent;
				$parent->children[$partialNamespace] = $child;
				$parent = $child;
				}
			else
				{
				$parent = $parent->children[$partialNamespace];
				}
			}
		// if we have a path, then we should add the class, otherwise we are just doing a lookup
		if ($path)
			{
			$parent->classes[$className] = $path;
			}

		return $parent;
		}

	/**
	 * Given a class, return it full path.
	 */
	public function getPathForClass(string $class) : string
		{
		if (isset($this->classes[$class]))
			{
			return $this->classes[$class];
			}

		throw new \Exception("Class {$class} not found in namespace {$this->getNamespace()}");
		}

	public static function getRoot() : NamespaceTree
		{
		if (! self::$root)
			{
			self::$root = new NamespaceTree();
			}

		return self::$root;
		}

	/**
	 * Populates a menu object with namespaces as sub menus and
	 * classes as menu items.
	 */
  public static function populateMenu(\PHPFUI\Menu $menu) : void
		{
		self::sort(self::getRoot());

		foreach (self::$root->children as $child)
			{
			$child->getMenuTree($child, $menu);
			}
    }

	/**
	 * Set the currently active class for menu generation.
	 */
	public static function setActiveClass(string $activeClass) : void
		{
		self::$activeClass = $activeClass;
		}

	/**
	 * Set the currently active namespace for menu generation.
	 */
	public static function setActiveNamespace(string $activeNamespace) : void
		{
		self::$activeNamespace = $activeNamespace;
		}

	/**
	 * Set the Controller. Used for creating links so all
	 * documentation is at the same url.
	 */
	public static function setController(Controller $controller) : void
		{
		self::$controller = $controller;
		}

	/**
	 * Sorts the child namespaces and classes
	 */
	public static function sort(NamespaceTree $tree = null) : void
		{
		if (! $tree)
			{
			$tree = self::getRoot();
			}
		ksort($tree->classes);
		ksort($tree->children);

		foreach ($tree->children as &$child)
			{
			self::sort($child);
			}
		}

	private function getMenuTree(NamespaceTree $tree, \PHPFUI\Menu $menu) : \PHPFUI\Menu
		{
		$currentMenu = new \PHPFUI\Menu();

		foreach ($tree->children as $child)
			{
			$namespace = $child->getNamespace();
			$menuItem = new \PHPFUI\MenuItem('\\' . $child->namespace);
			if ($namespace == self::$activeNamespace)
				{
				$menuItem->setActive();
				}
			$currentMenu->addSubMenu($menuItem, $this->getMenuTree($child, $currentMenu));
			}
		$namespace = $tree->getNamespace();

		foreach ($tree->classes as $class => $path)
			{
			$menuItem = new \PHPFUI\MenuItem($class, self::$controller->getClassURL($namespace . '\\' . $class));

			if ($class == self::$activeClass && $namespace == self::$activeNamespace)
				{
				$menuItem->setActive();
				}
			$currentMenu->addMenuItem($menuItem);
			}
		$menuItem = new \PHPFUI\MenuItem('\\' . $tree->namespace);
		$menu->addSubMenu($menuItem, $currentMenu);

		return $currentMenu;
		}

	}
