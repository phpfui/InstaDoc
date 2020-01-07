<?php

namespace PHPFUI\InstaDoc\Section;

class Doc extends \PHPFUI\InstaDoc\Section
	{

	private $factory;
	private $reflection;
	private $class;

	public function generate(\PHPFUI\Page $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$parameters = $this->controller->getParameters();
		$this->factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
		$parameters = $this->controller->getParameters();
		$this->class = $parameters[\PHPFUI\InstaDoc\Controller::NAMESPACE] . '\\' . $parameters[\PHPFUI\InstaDoc\Controller::CLASS_NAME];

		try
			{
			$this->reflection = new \ReflectionClass($this->class);
			}
		catch (\throwable $e)
			{
			$container->add(new \PHPFUI\SubHeader("{$this->class} is not a class"));

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

		$attributes = ['Abstract', 'Anonymous', 'Cloneable', 'Final', 'Instantiable', 'Interface', 'Internal', 'Iterable', 'Trait'];

		$row = new \PHPFUI\GridX();
		foreach ($attributes as $attribute)
			{
			$method = 'is' . $attribute;
			if ($this->reflection->$method())
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

		$tabs = new \PHPFUI\Tabs();
		$tabs->addTab('Public', $this->getContent('isPublic'), true);
		$tabs->addTab('Protected', $this->getContent('isProtected'));
		$tabs->addTab('Private', $this->getContent('isPrivate'));
		$tabs->addTab('Static', $this->getContent('isStatic'));

		$container->add($tabs);

		return $container;
		}

	private function getContent(string $accessType) : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();
		$table->addClass('hover');
		$table->addClass('unstriped');
		$table->addClass('stack');

		$constants = $this->reflection->getConstants();
		if ($constants)
			{
			ksort($constants);
			$section = 'Constants';
			foreach ($constants as $name => $value)
				{
				$constant = new \ReflectionClassConstant($this->class, $name);

				if ($accessType != 'isStatic' && $constant->$accessType())
					{
					$info = $this->getAccess($constant) . ' ' . $this->getColor('constant', $this->getColor('constant', $this->getName($constant, $name))) . ' = ' . $this->getValueString($value);

					$info .= $this->getComments($constant);

					$table->addRow([$this->section($section), $info]);
					$section = '';
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
				if ($property->$accessType())
					{
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

					$info .= $this->getComments($property);

					$table->addRow([$this->section($section), $info]);
					$section = '';
					}
				}
			}

		$methods = $this->reflection->getMethods();
		$types = ['public', 'protected', 'private', 'abstract', 'final', 'static'];
		if ($methods)
			{
			$this->objectSort($methods);
			$section = 'Methods';
			foreach ($methods as $method)
				{
				if ($method->$accessType())
					{
					$info = '';
					foreach ($types as $type)
						{
						$isType = 'is' . ucfirst($type);
						if ($method->$isType())
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
							if ($type->allowsNull())
								{
								$info .= '?';
								}
							$info .= $this->getColor('type', $this->getValueString($type));
							}
						else
							{
//							$info .= $this->getColor('type', 'mixed');
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
					$info .= $this->getComments($method);

					$table->addRow([$this->section($section), $info]);
					$section = '';
					}
				}
			}

		return $table;
		}

	private function getColor(string $class, string $name) : string
		{
		$span = new \PHPFUI\HTML5Element('span');
		$span->addClass($class);
		$span->add($name);

		return $span;
		}

	private function getAccess($constant) : string
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

	private function getClassName(string $class) : string
		{
		if (strpos($class, '\\') !== false)
			{
			return new \PHPFUI\Link($this->controller->getClassUrl($class), $class, false);
			}

		return $this->getColor('type', $class);
		}

	private function section(string $name) : string
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

	private function getName($method, string $name) : string
		{
		$parent = $method->getDeclaringClass();
		if ($parent->getName() != $this->reflection->getName())
			{
			$link = $this->getClassName($parent->getName());
			$name = $link . '::' . $name;
			}

		return $name;
		}

	private function getComments($method) : string
		{
		$comments = $method->getDocComment();
		if (! $comments)
			{
			return '';
			}

		$docblock = $this->factory->create($comments);

		$gridX = new \PHPFUI\GridX();
		$cell1 = new \PHPFUI\Cell(1);
		$cell1->add('&nbsp;');
		$gridX->add($cell1);
		$cell11 = new \PHPFUI\Cell(11);
		$cell11->add($docblock->getSummary());
		$gridX->add($cell11);

		return $gridX;
		}

	private function objectSort(array &$objects) : void
		{
		usort($objects, [$this, 'objectCompare']);
		}

	private function objectCompare($lhs, $rhs) : int
		{
		return $lhs->name <=> $rhs->name;
		}

	private function getValueString($value) : string
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
						$text .= $this->getValueString($key) . ' ';
						}
					++$index;
					$text .= $this->getColor('operator', '=>') . ' ' . $this->getValueString($part);
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
				if ($class == 'ReflectionNamedType')
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

	}
