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
//		$page->addStyleSheet("highlighter/styles/{$parameters['CSS']}.css");
		$page->addStyleSheet("highlighter/styles/qtcreator_light.css");
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

		$comments = $this->reflection->getDocComment();
		if ($comments)
			{
			$docblock = $this->factory->create($comments);
			$callout = new \PHPFUI\Callout('secondary');
			$callout->add($docblock->getSummary());
			$container->add($callout);
			}

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
					$info = $this->getAccess($constant) . ' ' . $this->getName($constant, $name) . ' = ' . $this->getValueString($value);

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
						$info .= 'static ';
						}
					$type = method_exists($property, 'getType') ? $property->getType() : '';
					if ($type)
						{
						$info .= $type->getName() . ' ';
						}

					$info .= $this->getName($property, '$' . $property->getName());

					$info .= $this->getComments($property);

					$table->addRow([$this->section($section), $info]);
					$section = '';
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
				if ($method->$accessType())
					{
					$info = $this->getAccess($method) . ' ';
					if ($method->isStatic())
						{
						$info .= 'static ';
						}

					$info .= $this->getName($method, $method->name) . '(';
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
							$info .= $this->getClassName($type);
							}
						else
							{
							$info .= 'mixed';
							}
						$info .= ' ';
						$info .= '$' . $parameter->getName();
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

	private function getAccess($constant) : string
		{
		$span = new \PHPFUI\HTML5Element('span');
		$span->addClass('hljs-keyword');

		if ($constant->isPrivate())
			{
			$span->add('private');
			}
		elseif ($constant->isProtected())
			{
			$span->add('protected');
			}
		else
			{
			$span->add('public');
			}

		return $span;
		}

	private function getClassName(string $class) : string
		{
		if (strpos($class, '\\') !== false)
			{
			return new \PHPFUI\Link($this->controller->getClassUrl($class), $class, false);
			}

		$span = new \PHPFUI\HTML5Element('span');
		$span->add($class);
		$span->addClass('hljs-type');

		return $span;
		}

	private function section(string $name) : string
		{
		if (! $name)
			{
			return $name;
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
				$text = '[';
				$comma = '';
				foreach ($value as $key => $part)
					{
					$text .= $comma . $this->getValueString($key) . ' => ' . $this->getValueString($part);
					$comma = ', ';
					}
				$text .= ']';
				$value = $text;
				break;
			case 'string':
				$span = new \PHPFUI\HTML5Element('span');
				$span->addClass('hljs-string');
				$span->add("'{$value}'");
				$value = $span;
				break;
			case 'object':
				$class = get_class($value);
				if ($class == 'ReflectionNamedType')
					{
					$value = ($value->allowsNull() ? '?' : '') . $value->getName();
					}
				else
					{
					$value = $this->getClassName(get_class($value));
					}
				break;
			case 'resource':
				$value = 'resource';
				break;
			case 'boolean':
				$value = $value ? 'true' : 'false';
				break;
			case 'NULL':
				$value = 'NULL';
				break;
			}

		return $value;
		}

	}
