<?php

namespace PHPFUI\InstaDoc\Section;

class Doc extends \PHPFUI\InstaDoc\Section
	{
	private $class;

	private $factory;
	private $reflection;

	public function generate(\PHPFUI\Page $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$this->factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
		$this->class = $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::NAMESPACE) . '\\' .
			$this->controller->getParameter(\PHPFUI\InstaDoc\Controller::CLASS_NAME);

		try
			{
			$this->reflection = new \ReflectionClass($this->class);
			}
		catch (\throwable $e)
			{
			$container->add($e->getMessage());

			return $container;
			}

		$comments = $this->reflection->getDocComment();

		if ($comments)
			{
			$docblock = $this->factory->create($comments);
			$callout = new \PHPFUI\Callout('secondary');
			$callout->add($docblock->getSummary());
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
			'Trait',
			];

		$row = new \PHPFUI\GridX();

		foreach ($attributes as $attribute)
			{
			$method = 'is' . $attribute;

			if ($this->reflection->{$method}())
				{
				$row->add($this->section($attribute));
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

		if ($parent)
			{
			$table->addRow([$this->section('Extends'), $this->getClassName($parent->getName())]);
			}

		$interfaces = $this->reflection->getInterfaces();

		if ($interfaces)
			{
			ksort($interfaces);
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

		if ($parent)
			{
			$parts = ['All', 'self'];

			while ($parent)
				{
				$parts[] = $parent->getName();
				$parent = $parent->getParentClass();
				}
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
							$this->getClass($name) . '").toggleClass("hide")');
					}
				$filterMenu->addMenuItem($menuItem);
				}
			array_shift($parts);
			$allSelector = '';
			$comma = '';

			foreach ($parts as $name)
				{
				$allSelector .= $comma . '.' . $this->getClass($name);
				$comma = ', ';
				}
			$allMenuItem->addAttribute('onclick', 'if($(this).hasClass("is-active")){$(".visMenu").removeClass("is-active");$("' .
					$allSelector . '").addClass("hide")}else{$(".visMenu").addClass("is-active");$("' . $allSelector .
					'").removeClass("hide")};$(this).toggleClass("is-active")');

			$container->add($filterMenu);
			}

		$tabs = new \PHPFUI\Tabs();
		$tabs->addTab('Public', $this->getContent('isPublic'), true);
		$tabs->addTab('Protected', $this->getContent('isProtected'));
		$tabs->addTab('Private', $this->getContent('isPrivate'));
		$tabs->addTab('Static', $this->getContent('isStatic'));

		$container->add($tabs);

		return $container;
		}

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

	/**
	 * Convert php class name to html class name (\ => -)
	 */
	protected function getClass(string $class) : string
		{
		return str_replace('\\', '-', $class);
		}

	protected function getClassName(string $class, bool $asLink = true) : string
		{
		if ($asLink && (false !== strpos($class, '\\')))
			{
			return new \PHPFUI\Link($this->controller->getClassUrl($class), $class, false);
			}

		return $this->getColor('type', $class);
		}

	protected function getColor(string $class, string $name) : string
		{
		$span = new \PHPFUI\HTML5Element('span');
		$span->addClass($class);
		$span->add($name);

		return $span;
		}


	protected function getComments(?\phpDocumentor\Reflection\DocBlock $docBlock) : string
		{
		if (! $docBlock)
			{
			return '';
			}

		$gridX = new \PHPFUI\GridX();
		$cell1 = new \PHPFUI\Cell(1);
		$cell1->add('&nbsp;');
		$gridX->add($cell1);
		$cell11 = new \PHPFUI\Cell(11);
		$cell11->add($docBlock->getSummary());
		$gridX->add($cell11);

		return $gridX;
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
			ksort($constants);
			$table->addRow([$this->section('Constants')]);

			foreach ($constants as $name => $value)
				{
				$constant = new \ReflectionClassConstant($this->class, $name);

				if ('isStatic' != $accessType && $constant->{$accessType}())
					{
					$table->addNextRowAttribute('class', $this->getRowClasses($constant));
					$table->addRow([$this->getConstant($constant, $name, $value)]);
					}
				}
			}

		$properties = $this->reflection->getProperties();

		if ($properties)
			{
			$this->objectSort($properties);
			$table->addRow([$this->section('Properties')]);

			foreach ($properties as $property)
				{
				if ($property->{$accessType}())
					{
					$table->addNextRowAttribute('class', $this->getRowClasses($property));
					$table->addRow([$this->getProperty($property)]);
					}
				}
			}

		$methods = $this->reflection->getMethods();

		if ($methods)
			{
			$this->objectSort($methods);
			$table->addRow([$this->section('Methods')]);

			foreach ($methods as $method)
				{
				if ($method->{$accessType}())
					{
					$table->addNextRowAttribute('class', $this->getRowClasses($method));
					$table->addRow([$this->getMethod($method)]);
					}
				}
			}

		return $table;
		}

	protected function getDocBlock($method) : ?\phpDocumentor\Reflection\DocBlock
		{
		$comments = $method->getDocComment();

		if (! $comments)
			{
			return null;
			}

		return $this->factory->create($comments);
		}

	protected function getMethod(\ReflectionMethod $method) : string
		{
		$docBlock = $this->getDocBlock($method);
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

		$info .= $this->getName($method, $this->getColor('name', $method->name)) . '(';
		$comma = '';

		foreach ($method->getParameters() as $parameter)
			{
			$info .= $comma;
			$comma = ', ';

			if ($parameter->hasType())
				{
				$type = $parameter->getType();
				$info .= $this->getColor('type', $this->getValueString($type));
				}
			$info .= ' ';
			$info .= $this->getColor('variable', '$' . $parameter->getName());

			if ($parameter->isDefaultValueAvailable())
				{
				$value = $parameter->getDefaultValue();
				$info .= ' = ' . $this->getValueString($value);
				}
			}
		$info .= ')';

		if ($method->hasReturnType())
			{
			$info .= ' : ' . $this->getClassName($method->getReturnType()->getName());
			}
		$info .= $this->getComments($docBlock);

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
		$info .= $this->getComments($docBlock);

		return $info;
		}

	protected function getRowClasses($method) : string
		{
		$class = $this->getNameScope($method);

		if (strlen($class))
			{
			return $this->getClass($class) . ' hide';
			}

		return 'self';
		}

	protected function getValueString($value) : string
		{
		switch (gettype($value))
			{
			case 'array':
				$index = 0;
				$text = $this->getColor('operator', '[');
				$comma = '';

				foreach ($value as $key => $part)
					{
					$text .= $comma;

					if ($index != $key)
						{
						$text .= $this->getValueString($key) . ' ' . $this->getColor('operator', '=>') . ' ';
						}
					++$index;
					$text .= $this->getValueString($part);
					$comma = ', ';
					}
				$text .= $this->getColor('operator', ']');
				$value = $text;

				break;

			case 'string':
				$value = $this->getColor('string', "'{$value}'");

				break;

			case 'object':
				$class = get_class($value);

				if ('ReflectionNamedType' == $class)
					{
					$value = ($value->allowsNull() ? '?' : '') . $this->getClassName($value->getName());
					}
				else
					{
					$value = $this->getClassName(get_class($value));
					}

				break;

			case 'resource':
				$value = $this->getColor('keyword', 'resource');

				break;

			case 'boolean':
				$value = $this->getColor('keyword', $value ? 'true' : 'false');

				break;

			case 'NULL':
				$value = $this->getColor('keyword', 'NULL');

				break;

			default:
				$value = $this->getColor('number', $value);
			}

		return $value;
		}

	protected function objectCompare($lhs, $rhs) : int
		{
		return $lhs->name <=> $rhs->name;
		}

	protected function objectSort(array &$objects) : void
		{
		usort($objects, [$this, 'objectCompare']);
		}

	protected function section(string $name) : string
		{
		if (! $name)
			{
			return '';
			}

		$section = new \PHPFUI\HTML5Element('span');
		$section->add($name);
		$section->addClass('callout');
		$section->addClass('small');
		$section->addClass('primary');

		return $section;
		}

	}
