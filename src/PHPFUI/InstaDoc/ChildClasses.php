<?php

namespace PHPFUI\InstaDoc;

/**
 * ChildClasses keeps track of all the children for a class. It uses the previously generated NamespaceTree information.
 */
class ChildClasses
	{
	/**
	 * @var array indexed by fqn of class containing array of fqn of children
	  */
	private static $children = [];

	/**
	 * Generate child class information from all classes in NamespaceTree
	 */
	public static function generate() : void
		{
		$classes = NamespaceTree::getAllClasses();

		foreach ($classes as $class)
			{
			try
				{
				$reflection = new \ReflectionClass($class);
				$parent = $reflection->getParentClass();

				if ($parent)
					{
					$parentName = $parent->getName();

					if (isset(self::$children[$parentName]))
						{
						self::$children[$parentName][] = $reflection->getName();
						}
					else
						{
						self::$children[$parentName] = [$reflection->getName()];
						}
					}
				}
			catch (\Throwable $e)
				{
				}
			}
		}

	/**
	 * Return the child classes for the passed fully qualified name
	 */
	public static function getChildClasses(string $fqn) : array
		{
		return self::$children[$fqn] ?? [];
		}

	/**
	 * Load the ChildClasses data, will generate it and create the file if the file does not exist.
	 */
	public static function load(string $file = '../ChildClasses.serial') : bool
		{
		if (! file_exists($file))
			{
			self::generate();

			return self::save($file);
			}

		$contents = file_get_contents($file);
		$temp = unserialize($contents);

		if (! $temp)
			{
			return false;
			}

		self::$children = $temp;

		return true;
		}

	/**
	 * Save the generated ChildClasses to the file specified
	 */
	public static function save(string $file = '../ChildClasses.serial') : bool
		{
		foreach (self::$children as &$childClasses)
			{
			sort($childClasses);
			}

		return file_put_contents($file, serialize(self::$children)) > 0;
		}
	}
