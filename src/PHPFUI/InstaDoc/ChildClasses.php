<?php

namespace PHPFUI\InstaDoc;

class ChildClasses
	{
	/**
	 * @var array indexed by fqn of class containing array of fqn of children
	  */
	private static $children = [];

	public static function generate() : self
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

		return __CLASS__;
		}

	public static function load(string $file = '../ChildClasses.serial') : bool
		{
		if (! file_exists($file))
			{
			self::generate();

			return $this->save($file);
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

	public static function save(string $file = '../ChildClasses.serial') : bool
		{
		foreach (self::$children as &$childClasses)
			{
			sort($childClasses);
			}

		return file_put_contents($file, serialize(self::$children)) > 0;
		}

	public static function getChildClasses(string $fqn) : array
		{
		return self::$children[$fqn] ?? [];
		}

	}
