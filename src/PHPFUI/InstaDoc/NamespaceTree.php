<?php

namespace PHPFUI\InstaDoc;

class NamespaceTree
	{
	private static $activeClass;
	private static $activeNamespace;

	/**
	 * @var array indexed by namespace part containing a NamespaceTree
	  */
	private $children = [];

	/**
	 * @var array indexed by fully qualified class name containing the file name
	 */
	private $classes = [];
	private static $controller;

	/**
	 * @var bool true if this namespace is in the local git repo
	 */
	private $localGit = false;

	/**
	 * @var array of unique markdown files indexed by file name
	 */
	private $md = [];

	/**
	 * @var string of the namespace part
	 */
	private $namespace = '';

	/**
	 * @var NamespaceTree our parent
	 */
	private $parent = null;
	private static $root = null;

	// only we can make us to ensure the tree is good
	private function __construct()
		{
		}

	public static function addNamespace(string $namespace, string $directory, bool $localGit = false) : void
		{
		$namespaceLength = strlen($namespace);

		if ($namespaceLength && '\\' == $namespace[$namespaceLength - 1])
			{
			$namespace = substr($namespace, 0, $namespaceLength - 1);
			}

		$node = self::findNamespace($namespace);
		$node->localGit = $localGit;

    $iterator = new \DirectoryIterator($directory);

    foreach ($iterator as $fileinfo)
			{
			$filename = $fileinfo->getFilename();
			$filenameLength = strlen($filename);

      if ($fileinfo->isDir() && false === strpos($filename, '.'))
				{
				self::addNamespace($namespace . '\\' . $filename, $directory . '/' . $filename, $localGit);
        }
			elseif (strpos($filename, '.php') == $filenameLength - 4)
				{
				$class = substr($filename, 0, $filenameLength - 4);
				$class = $namespace . '\\' . $class;
				$file = $directory . '/' . $filename;
				$file = str_replace('//', '/', $file);
				$node->classes[$class] = $file;
				}
			elseif (strpos($filename, '.md') == $filenameLength - 3)
				{
				$node->md[$directory . '/' . $filename] = true;
				}
			}
		}

	public static function deleteNameSpace(string $namespace) : void
		{
		$deleteThis = self::findNamespace($namespace);
		unset($deleteThis->parent->children[$namespace], $deleteThis);

		}

	public static function findNamespace(string $namespace) : NamespaceTree
		{
		$node = self::getRoot();

		if (! strlen($namespace))
			{
			return $node;
			}

		$parts = explode('\\', $namespace);

		foreach ($parts as $part)
			{
			if (empty($node->children[$part]))
				{
				$child = new NamespaceTree();
				$child->namespace = $part;
				$node->children[$part] = $child;
				$child->parent = $node;
				}
			$node = $node->children[$part];
			}

		return $node;
		}

	/**
	 * Returns array of all classes
	 */
  public static function getAllClasses(?NamespaceTree $tree = null) : array
		{
		if (! $tree)
			{
			$tree = self::getRoot();
			}

		$classes = [];

		foreach ($tree->children as $child)
			{
			$classes = array_merge($classes, self::getAllClasses($child));
			}

		$namespace = $tree->getNamespace();

		foreach ($tree->classes as $class => $path)
			{
			$classes[$path] = $class;
			}

		return $classes;
		}

	public static function getAllMDFiles(?NamespaceTree $tree = null) : array
		{
		if (! $tree)
			{
			$tree = self::getRoot();
			}
		$files = $tree->getMDFiles();

		foreach ($tree->children as $child)
			{
			$files = array_merge($files, self::getAllMDFiles($child));
			}

		return $files;
		}

	public function getChildren() : array
		{
		return $this->children;
		}

	/**
	 * Return an array with full paths of all the classes in the
	 * namespace, indexed by class name
	 */
  public function getClassFilenames() : array
		{
		return $this->classes;
		}

	public function getGit() : bool
		{
		return $this->localGit;
		}

	public function getMDFiles() : array
		{
		return array_keys($this->md);
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

	public static function load(string $file) : bool
		{
		if (! file_exists($file))
			{
			return false;
			}

		$contents = file_get_contents($file);
		$temp = unserialize($contents);

		if (! $temp)
			{
			return false;
			}

		self::$root = $temp;

		return true;
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

	public static function save(string $file) : bool
		{
		return file_put_contents($file, serialize(self::$root)) > 0;
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
		if (strlen($activeNamespace) && '\\' != $activeNamespace[0])
			{
			$activeNamespace = '\\' . $activeNamespace;
			}

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
			$parts = explode('\\', $class);
			$baseClass = array_pop($parts);
			$menuItem = new \PHPFUI\MenuItem($baseClass, self::$controller->getClassURL($class));

			if ($baseClass == self::$activeClass && $namespace == self::$activeNamespace)
				{
				$menuItem->setActive();
				}
			$currentMenu->addMenuItem($menuItem);
			}
		$menuItem = new \PHPFUI\MenuItem('\\' . $tree->namespace);
		$menu->addSubMenu($menuItem, $currentMenu);

		return $currentMenu;
		}

	private static function getRoot() : NamespaceTree
		{
		if (! self::$root)
			{
			self::$root = new NamespaceTree();
			}

		return self::$root;
		}

	}
