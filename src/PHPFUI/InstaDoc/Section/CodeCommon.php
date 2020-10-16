<?php

namespace PHPFUI\InstaDoc\Section;

class CodeCommon extends \PHPFUI\InstaDoc\Section
	{
	protected $factory;
	protected $parsedown;
	protected $reflection;

	public function __construct(\PHPFUI\InstaDoc\Controller $controller)
		{
		parent::__construct($controller);
		$this->factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
		$this->parsedown = new \PHPFUI\InstaDoc\MarkDownParser();
		}

	/**
	 * Format comments without indentation
	 */
	protected function formatComments(?\phpDocumentor\Reflection\DocBlock $docBlock, ?\ReflectionMethod $reflectionMethod = null) : string
		{
		if (! $docBlock)
			{
			return '';
			}

		$container = new \PHPFUI\Container();

		$container->add($this->parsedown->text($docBlock->getSummary()));
		$desc = $docBlock->getDescription();

		if ($desc)
			{
			$div = new \PHPFUI\HTML5Element('div');
			$div->addClass('description');
			$div->add($this->parsedown->text($desc));
			$container->add($div);
			}

		$tags = $docBlock->getTags();
		// if we are in a method, inheritdoc makes sense, and we should get the correct doc block comments
		if ($reflectionMethod)
			{
			$tags = $this->getInheritedDocBlock($tags, $reflectionMethod);
			}

		if ($tags)
			{
			$ul = new \PHPFUI\UnorderedList();

			foreach ($tags as $tag)
				{
				$name = $tag->getName();
				$description = method_exists($tag, 'getDescription') ? trim($tag->getDescription()) : '';
				$body = '';
				// punt on useless tags
				if (in_array($name, ['method', 'param', 'inheritdoc']))
					{
					continue;
					}

				if ('var' == $name)
					{
					// useless if no description or type
					if (! $description && ! $tag->getType())
						{
						continue;
						}
					}

				if (method_exists($tag, 'getAuthorName'))
					{
					$body .= \PHPFUI\Link::email($tag->getEmail(), $tag->getAuthorName());
					}

				if (method_exists($tag, 'getReference'))
					{
					$body .= $tag->getReference();
					}

				if (method_exists($tag, 'getVersion'))
					{
					$body .= $tag->getVersion();
					}

				if (method_exists($tag, 'getLink'))
					{
					$body .= new \PHPFUI\Link($tag->getLink(), '', false);
					}

				if (method_exists($tag, 'getType'))
					{
					$type = $tag->getType();

					if ($type)
						{
						$body .= $this->getClassName($type) . ' ';
						}
					}

				if (method_exists($tag, 'getVariableName'))
					{
					$varname = $tag->getVariableName();

					if ($varname)
						{
						$body .= $this->getColor('variable', '$' . $varname) . ' ';
						}
					}
				$body .= $description;
				$ul->addItem(new \PHPFUI\ListItem($this->getColor('name', $name) . ' ' . $this->getColor('description', $body)));
				}

			$container->add($ul);
			}

		return $container;
		}

	protected function getClassName(string $class, bool $asLink = true) : string
		{
		$array = '';

		if ($asLink && $class)
			{
			// could be mixed, break out by |
			$parts = explode('|', $class);

			if (count($parts) > 1)
				{
				$returnValue = [];

				foreach ($parts as $part)
					{
					$returnValue[] = $this->getClassName($part, true);
					}

				return implode('|', $returnValue);
				}
			else
				{
				if ('\\' == $class[0])
					{
					$class = substr($class, 1);
					}

				if (false !== strpos($class, '[]'))
					{
					$array = '[]';
					$class = str_replace($array, '', $class);
					}
				// if fully qualified, we are done
				if (\PHPFUI\InstaDoc\NamespaceTree::hasClass($class))
					{
					return new \PHPFUI\Link($this->controller->getClassUrl($class), $class, false) . $array;
					}

				// try name in current namespace tree
				$namespacedClass = $this->reflection->getNamespaceName() . '\\' . $class;

				if (\PHPFUI\InstaDoc\NamespaceTree::hasClass($namespacedClass))
					{
					return new \PHPFUI\Link($this->controller->getClassUrl($namespacedClass), $namespacedClass, false) . $array;
					}
				}
			}

		return $this->getColor('type', $class) . $array;
		}

	/**
	 * Add a color to a thing by class
	 */
	protected function getColor(string $class, string $name) : string
		{
		$span = new \PHPFUI\HTML5Element('span');
		$span->addClass($class);
		$span->add($name);

		return $span;
		}

	/**
	 * Get comments indented
	 */
	protected function getComments(?\phpDocumentor\Reflection\DocBlock $docBlock, ?\ReflectionMethod $reflectionMethod = null) : string
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
		$cell11->add($this->formatComments($docBlock, $reflectionMethod));
		$gridX->add($cell11);

		return $gridX;
		}

	protected function getDocBlock($method) : ?\phpDocumentor\Reflection\DocBlock
		{
		$comments = $method->getDocComment();

		if (! $comments)
			{
			return null;
			}

		try
			{
			$docBlock = $this->factory->create($comments);
			}
		catch (\Exception $e)
			{
			$docBlock = null;
			}

		return $docBlock;
		}

	/**
	 * Convert php class name to html class name (\ => -)
	 */
	protected function getHtmlClass(string $class) : string
		{
		return str_replace('\\', '-', $class);
		}

	protected function getInheritedDocBlock(array $tags, \ReflectionMethod $reflectionMethod) : array
		{
		foreach ($tags as $index => $tag)
			{
			if (0 == strcasecmp($tag->getName(), 'inheritDoc'))
				{
				$reflectionClass = $reflectionMethod->getDeclaringClass();
				$parent = $reflectionClass->getParentClass();

				if (! $parent)
					{
					return [];	// no parent, at top of tree, and no tags, go figure
					}
				$method = $parent->getMethod($reflectionMethod->name);

				if (! $method)
					{
					return [];	// no method here, kinda strange
					}
				$docBlock = $this->getDocBlock($method);

				if ($docBlock)
					{
					// add in the new tags and check parent
					array_splice ($tags, $index, 1, $docBlock->getTags());

					return $this->getInheritedDocBlock($tags, $method);
					}

				// Nothing at this level, but go up one and try the parent method
				return $this->getInheritedDocBlock($tags, $method);
				}
			}

		return $tags;
		}

	protected function getParameterComments(?\phpDocumentor\Reflection\DocBlock $docBlock) : array
		{
		$comments = [];

		if (! $docBlock)
			{
			return $comments;
			}

		foreach ($docBlock->getTags() as $tag)
			{
			$name = $tag->getName();
			$description = method_exists($tag, 'getDescription') ? trim($tag->getDescription()) : '';

			if ('param' == $name && $description)
				{
				$var = $tag->getVariableName();
				$comments[$var] = $description;
				}
			}

		return $comments;
		}

	/**
	 * @param \ReflectionFunction | \ReflectionMethod $method
	 */
	protected function getParameters($method) : string
		{
		$info = '(';
		$comma = '';
		$docBlock = $this->getDocBlock($method);

		$parameterComments = $this->getParameterComments($docBlock);

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

			$name = $parameter->getName();
			$tip = '$' . $name;

			if (isset($parameterComments[$name]))
				{
				$tip = new \PHPFUI\ToolTip($tip, $parameterComments[$name]);
				}
			$info .= $this->getColor('variable', $tip);

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

		$info .= $this->getComments($docBlock, $method instanceof \ReflectionMethod ? $method : null);

		return $info;
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

					if ($index !== $key)
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
