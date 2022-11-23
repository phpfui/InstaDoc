<?php

namespace PHPFUI\InstaDoc\Section;

class Doc extends \PHPFUI\InstaDoc\Section\CodeCommon
	{
	private string $class;

	public function __construct(\PHPFUI\InstaDoc\Controller $controller)
		{
		parent::__construct($controller);
		}

	public function generate(\PHPFUI\InstaDoc\PageInterface $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$this->class = $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::NAMESPACE) . '\\' . $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::CLASS_NAME);

		try
			{
			$this->reflection = new \ReflectionClass($this->class);

			try
				{
				// @phpstan-ignore-next-line
				$this->reflection->isInstantiable();
				}
			catch (\Throwable)
				{
				$this->reflection = new \ReflectionEnum($this->class);
				}
			}
		catch (\Throwable)
			{
			// Try to parse as functions
			$functionView = new \PHPFUI\InstaDoc\Section\Functions($this->controller);
			$container->add($functionView->generate($page, $fullClassPath));

			return $container;
			}

		$comments = $this->reflection->getDocComment();
		$comments = \str_replace('{@inheritdoc}', '@inheritdoc', $comments);

		if ($comments)
			{
			$docblock = $this->factory->create($comments);
			$callout = new \PHPFUI\Callout('secondary');
			$callout->add($this->formatComments($docblock, $this->reflection));
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

			if (\method_exists($this->reflection, $method))
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

		$accordion = new \PHPFUI\Accordion();
		$accordion->addAttribute('data-multi-expand', 'true');
		$accordion->addAttribute('data-allow-all-closed', 'true');

		$table = new \PHPFUI\Table();
		$table->addClass('hover');
		$table->addClass('unstriped');

		foreach (\array_reverse($parentNames) as $name)
			{
			$table->addRow([$this->getClassName($name)]);
			}

		if (\count($table))
			{
			$accordion->addTab('Extends', $table);
			}

		$table = new \PHPFUI\Table();
		$table->addClass('hover');
		$table->addClass('unstriped');

		foreach (\PHPFUI\InstaDoc\ChildClasses::getChildClasses($this->class) as $class)
			{
			$table->addRow([$this->getClassName($class)]);
			}

		if (\count($table))
			{
			$accordion->addTab('Children', $table);
			}

		$interfaces = $this->reflection->getInterfaces();

		if ($interfaces)
			{
			\ksort($interfaces, SORT_FLAG_CASE | SORT_STRING);
			$table = new \PHPFUI\Table();
			$table->addClass('hover');
			$table->addClass('unstriped');

			foreach ($interfaces as $interface)
				{
				$table->addRow([$this->getClassName($interface->getName())]);
				}

			if (\count($table))
				{
				$accordion->addTab('Implements', $table);
				}
			}

		$traits = $this->getTraits($this->reflection);

		if ($traits)
			{
			\ksort($traits, SORT_FLAG_CASE | SORT_STRING);
			$table = new \PHPFUI\Table();
			$table->addClass('hover');
			$table->addClass('unstriped');

			foreach ($traits as $trait)
				{
				$table->addRow([$this->getClassName($trait->getName())]);
				}

			if (\count($table))
				{
				$accordion->addTab('Traits', $table);
				}
			}

		$reflectionAttributes = $this->getAttributes($this->reflection);

		if ($reflectionAttributes)
			{
			$table = new \PHPFUI\Table();
			$table->addClass('hover');
			$table->addClass('unstriped');

			foreach ($reflectionAttributes as $attribute)
				{
				$table->addRow([$this->formatAttribute($attribute)]);
				}

			if (\count($table))
				{
				$accordion->addTab('Attributes', $table);
				}
			}

		if (\count($accordion))
			{
			$container->add($accordion);
			}

		$parent = $this->reflection->getParentClass();

		$allMenuItem = null;

		if ($parentNames)
			{
			$parts = \array_merge(['All', 'self'], $parentNames);

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
			\array_shift($parts);
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

			if (\count($table))
				{
				$tabs->addTab($section, $table, $first);
				$first = false;
				}
			}
		$container->add($tabs);

		return $container;
		}

	/**
	 * @return string the color coded access level (public, private, protected)
	 */
	protected function getAccess(\ReflectionProperty|\ReflectionClassConstant $constant) : string
		{
		if ($constant->isPrivate())
			{
			$type = $this->getColor('keyword', 'private');
			}
		elseif ($constant->isProtected())
			{
			$type = $this->getColor('keyword', 'protected');
			}
		else
			{
			$type = $this->getColor('keyword', 'public');
			}

		if (\method_exists($constant, 'isReadOnly') && $constant->isReadOnly())
			{
			$type .= $this->getColor('keyword', 'readonly');
			}

		return $type;
		}

	protected function getConstant(\ReflectionClassConstant $constant, string $name, mixed $value) : string
		{
		/**
		 * @todo get attributes everywhere
		 * $attributes = $this->getAttributes($constant);
		 */
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

		if ($constants && 'isStatic' != $accessType)
			{
			\ksort($constants, SORT_FLAG_CASE | SORT_STRING);
			$section = 'Constants';

			foreach ($constants as $name => $value)
				{
				$constant = new \ReflectionClassConstant($this->class, $name);

				/**
				 * @todo get attributes everywhere
				 * $attributes = $this->getAttributes($constant);
				 */
				if ($constant->{$accessType}())
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
				if ($property->{$accessType}())
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
				if ($method->{$accessType}())
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
			$isType = 'is' . \ucfirst($type);

			if ($method->{$isType}())
				{
				$info .= $this->getColor('keyword', $type) . ' ';
				}
			}

		$info .= $this->getName($method, $this->getColor('name', $method->name));
		$info .= $this->getMethodParametersBlock($method);

		return $info;
		}

	protected function getName(\ReflectionClassConstant|\ReflectionMethod|\ReflectionProperty $method, string $name, bool $fullyQualify = false) : string
		{
		$parent = $this->getNameScope($method, $fullyQualify);

		if ($parent)
			{
			$link = $this->getClassName($parent, $fullyQualify);
			$name = $link . '::' . $name;
			}

		return $name;
		}

	protected function getNameScope(\ReflectionProperty|\ReflectionMethod|\ReflectionClassConstant $method, bool $fullyQualify = false) : string
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
		$type = \method_exists($property, 'getType') ? $property->getType() : '';

		if ($type)
			{
			// @phpstan-ignore-next-line
			$info .= $this->getColor('type', $this->getClassName($type->getName())) . ' ';
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

	protected function getRowClasses(\ReflectionClassConstant|\ReflectionMethod|\ReflectionProperty $method) : string
		{
		$class = $this->getNameScope($method);

		if (\strlen($class))
			{
			return $this->getHtmlClass($class) . ' hide';
			}

		return 'self';
		}

	protected function objectCompare(object $lhs, object $rhs) : int
		{
		return \strcasecmp($lhs->name, $rhs->name);
		}

	/**
	 * @param array<object> $objects to sort
	 */
	protected function objectSort(array &$objects) : void
		{
		\usort($objects, [$this, 'objectCompare']);
		}

	/**
	 * @param \ReflectionClass<object> $reflection
	 *
	 * @return array<string, \ReflectionClass<object>> array of traits for the entire inheritance tree, not just the current class
	 */
	private function getTraits(\ReflectionClass $reflection) : array
		{
		$traits = [];

		$parent = $reflection->getParentClass();

		if ($parent)
			{
			$traits = $this->getTraits($parent);
			}

		return \array_merge($traits, $reflection->getTraits());
		}
	}
