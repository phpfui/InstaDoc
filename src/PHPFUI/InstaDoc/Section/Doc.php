<?php

namespace PHPFUI\InstaDoc\Section;

class Doc extends \PHPFUI\InstaDoc\Section\CodeCommon
	{
	private $class;

	public function __construct(\PHPFUI\InstaDoc\Controller $controller)
		{
		parent::__construct($controller);
		}

	public function generate(\PHPFUI\Instadoc\PageInterface $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$this->class = $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::NAMESPACE) . '\\' . $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::CLASS_NAME);

		try
			{
			$this->reflection = new \ReflectionClass($this->class);
			}
		catch (\throwable $e)
			{
			// Try to parse as functions
			$functionView = new \PHPFUI\InstaDoc\Section\Functions($this->controller);
			$container->add($functionView->generate($page, $fullClassPath));

			return $container;
			}

		$comments = $this->reflection->getDocComment();

		if ($comments)
			{
			$docblock = $this->factory->create($comments);
			$callout = new \PHPFUI\Callout('secondary');
			$callout->add($this->formatComments($docblock));
			$container->add($callout);
			}

		$attributes = [
			'Abstract',
			'Anonymous',
			'Cloneable',
			'Final',
			'Instantiable',
			'Interface',
			'Internal',
			'Iterable',
			'Promoted',
			'Trait',
			];

		$row = new \PHPFUI\GridX();

		foreach ($attributes as $attribute)
			{
			$method = 'is' . $attribute;

			if (method_exists($this->reflection, $method))
				{
				if ($this->reflection->{$method}())
					{
					$row->add($this->section($attribute));
					}
				}
			}

		if ($row->count())
			{
			$container->add($row);
			}

		$table = new \PHPFUI\Table();
		$table->addClass('hover');
		$table->addClass('unstriped');
		$table->addClass('stack');

		$parent = $this->reflection->getParentClass();

		$parentNames = [];

		if ($parent)
			{
			while ($parent)
				{
				$parentNames[] = $parent->getName();
				$parent = $parent->getParentClass();
				}
			}

		$extends = $this->section('Extends');

		foreach (array_reverse($parentNames) as $name)
			{
			$table->addRow([$extends, $this->getClassName($name)]);
			$extends = '';
			}

		$interfaces = $this->reflection->getInterfaces();

		if ($interfaces)
			{
			ksort($interfaces, SORT_FLAG_CASE | SORT_STRING);
			$section = 'Implements';

			foreach ($interfaces as $interface)
				{
				$class = $interface->getName();
				$table->addRow([$this->section($section), $this->getClassName($interface->getName())]);
				$section = '';
				}
			}

		$container->add($table);

		$parent = $this->reflection->getParentClass();

		if ($parentNames)
			{
			$parts = array_merge(['All', 'self'], $parentNames);

			$filterMenu = new \PHPFUI\Menu();

			foreach ($parts as $name)
				{
				$menuItem = new \PHPFUI\MenuItem($name, '#');

				if ('All' == $name)
					{
					$allMenuItem = $menuItem;
					}
				else
					{
					if ('self' == $name)
						{
						$menuItem->setActive();
						}
					$menuItem->addClass('visMenu');
					$menuItem->addAttribute('onclick', '$(this).toggleClass("is-active");$(".' .
							$this->getHtmlClass($name) . '").toggleClass("hide")');
					}
				$filterMenu->addMenuItem($menuItem);
				}
			array_shift($parts);
			$allSelector = '';
			$comma = '';

			foreach ($parts as $name)
				{
				$allSelector .= $comma . '.' . $this->getHtmlClass($name);
				$comma = ', ';
				}
			$allMenuItem->addAttribute('onclick', 'if($(this).hasClass("is-active")){$(".visMenu").removeClass("is-active");$("' .
					$allSelector . '").addClass("hide")}else{$(".visMenu").addClass("is-active");$("' . $allSelector .
					'").removeClass("hide")};$(this).toggleClass("is-active")');

			$container->add($filterMenu);
			}

		$tabs = new \PHPFUI\Tabs();
		$first = true;

		foreach ($this->controller->getAccessTabs() as $section)
			{
			$table = $this->getContent('is' . $section);

			if (count($table))
				{
				$tabs->addTab($section, $table, $first);
				$first = false;
				}
			}
		$container->add($tabs);

		return $container;
		}

	/**
	 * Return the color coded access level (public, private, protected)
	 */
	protected function getAccess($constant) : string
		{
		if ($constant->isPrivate())
			{
			return $this->getColor('keyword', 'private');
			}
		elseif ($constant->isProtected())
			{
			return $this->getColor('keyword', 'protected');
			}

		return $this->getColor('keyword', 'public');
		}

	protected function getConstant(\ReflectionClassConstant $constant, string $name, $value) : string
		{
		$docBlock = $this->getDocBlock($constant);
		$info = $this->getAccess($constant) . ' ' . $this->getColor('constant', $this->getColor('constant', $this->getName($constant, $name, true))) . ' = ' . $this->getValueString($value);
		$info .= $this->getComments($docBlock);

		return $info;
		}

	protected function getContent(string $accessType) : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();
		$table->addClass('hover');
		$table->addClass('unstriped');
		$table->addClass('stack');

		$constants = $this->reflection->getConstants();

		if ($constants)
			{
			ksort($constants, SORT_FLAG_CASE | SORT_STRING);
			$section = 'Constants';

			foreach ($constants as $name => $value)
				{
				$constant = new \ReflectionClassConstant($this->class, $name);

				if (method_exists($constant, $accessType) && $constant->{$accessType}())
					{
					if ($section)
						{
						$table->addRow([$this->section($section)]);
						$section = '';
						}

					$table->addNextRowAttribute('class', $this->getRowClasses($constant));
					$table->addRow([$this->getConstant($constant, $name, $value)]);
					}
				}
			}

		$properties = $this->reflection->getProperties();

		if ($properties)
			{
			$this->objectSort($properties);
			$section = 'Properties';

			foreach ($properties as $property)
				{
				if (method_exists($property, $accessType) && $property->{$accessType}())
					{
					if ($section)
						{
						$table->addRow([$this->section($section)]);
						$section = '';
						}

					$table->addNextRowAttribute('class', $this->getRowClasses($property));
					$table->addRow([$this->getProperty($property)]);
					}
				}
			}

		$methods = $this->reflection->getMethods();

		if ($methods)
			{
			$this->objectSort($methods);
			$section = 'Methods';

			foreach ($methods as $method)
				{
				if (method_exists($method, $accessType) && $method->{$accessType}())
					{
					if ($section)
						{
						$table->addRow([$this->section($section)]);
						$section = '';
						}

					$table->addNextRowAttribute('class', $this->getRowClasses($method));
					$table->addRow([$this->getMethod($method)]);
					}
				}
			}

		return $table;
		}

	protected function getMethod(\ReflectionMethod $method) : string
		{
		$info = '';
		$types = ['public', 'protected', 'private', 'abstract', 'final', 'static'];

		foreach ($types as $type)
			{
			$isType = 'is' . ucfirst($type);

			if ($method->{$isType}())
				{
				$info .= $this->getColor('keyword', $type) . ' ';
				}
			}

		$info .= $this->getName($method, $this->getColor('name', $method->name));
		$info .= $this->getParameters($method);

		return $info;
		}


	protected function getName($method, string $name, bool $fullyQualify = false) : string
		{
		$parent = $this->getNameScope($method, $fullyQualify);

		if ($parent)
			{
			$link = $this->getClassName($parent, ! $fullyQualify);
			$name = $link . '::' . $name;
			}

		return $name;
		}

	protected function getNameScope($method, bool $fullyQualify = false) : string
		{
		$parent = $method->getDeclaringClass();

		if ($fullyQualify || ($parent->getName() != $this->reflection->getName()))
			{
			return $parent->getName();
			}

		return '';
		}

	protected function getProperty(\ReflectionProperty $property) : string
		{
		$property->setAccessible(true);
		$docBlock = $this->getDocBlock($property);
		$info = $this->getAccess($property) . ' ';

		if ($property->isStatic())
			{
			$info .= $this->getColor('keyword', 'static') . ' ';
			}
		$type = method_exists($property, 'getType') ? $property->getType() : '';

		if ($type)
			{
			$info .= $this->getColor('type', $type->getName()) . ' ';
			}
		$info .= $this->getName($property, $this->getColor('variable', '$' . $property->getName()));
		if ($property->isStatic())
			{
			$value = $property->getValue();
			if ($value)
				{
				$info .= ' = ' . $this->getValueString($value);
				}
			}

		$info .= $this->getComments($docBlock);

		return $info;
		}

	protected function getRowClasses($method) : string
		{
		$class = $this->getNameScope($method);

		if (strlen($class))
			{
			return $this->getHtmlClass($class) . ' hide';
			}

		return 'self';
		}

	protected function objectCompare($lhs, $rhs) : int
		{
		return strcasecmp($lhs->name, $rhs->name);
		}

	protected function objectSort(array &$objects) : void
		{
		usort($objects, [$this, 'objectCompare']);
		}

	}
