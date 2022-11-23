<?php

namespace PHPFUI\InstaDoc\Section;

class CodeCommon extends \PHPFUI\InstaDoc\Section
	{
	protected \phpDocumentor\Reflection\DocBlockFactory $factory;

	protected \PHPFUI\InstaDoc\MarkDownParser $parsedown;

	/**
	 * @var \ReflectionClass<object> | \ReflectionEnum | \ReflectionFunction
	 */
	protected $reflection;

	public function __construct(\PHPFUI\InstaDoc\Controller $controller, string $fullClassPath = '')
		{
		parent::__construct($controller);
		$this->factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
		$this->parsedown = new \PHPFUI\InstaDoc\MarkDownParser();

		if ($fullClassPath)
			{
			try
				{
				$this->reflection = new \ReflectionClass($fullClassPath);

				try
					{
					// @phpstan-ignore-next-line
					$this->reflection->isInstantiable();
					}
				catch (\Throwable)
					{
					$this->reflection = new \ReflectionEnum($fullClassPath);
					}
				}
			catch (\Throwable)
				{
				}
			}
		}

	/**
	 * @param array<string, string> $parameterComments comments indexed by parameter name
	 */
	public function getMethodParameters(\ReflectionFunction|\ReflectionMethod $method, array $parameterComments = []) : string
		{
		$info = $comma = '';

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

			/**
			 * @todo add attributes for parameters
			 * $attributes = $this->getAttributes($parameter);
			 */
			if (isset($parameterComments[$name]))
				{
				$tip = new \PHPFUI\ToolTip($tip, \htmlspecialchars($parameterComments[$name]));
				$tip->addAttribute('data-allow-html');
				}
			$info .= $this->getColor('variable', $tip);

			if ($parameter->isDefaultValueAvailable())
				{
				$value = $parameter->getDefaultValue();
				$info .= ' = ' . $this->getValueString($value);

				if ($parameter->isDefaultValueConstant())
					{
					if (\is_object($value))
						{
						$value = $value::class;
						}
					$extra = $parameter->getDefaultValueConstantName();
					$info .= \str_replace($value, '', $extra);
					}
				}
			}

		return $info;
		}

	public function getValueString(mixed $value) : string
		{
		switch (\gettype($value))
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
				$value = \htmlspecialchars($value);
				$value = $this->getColor('string', "'{$value}'");

				break;

			case 'object':
				$class = $value::class;

				if ('ReflectionNamedType' == $class)
					{
					$value = ($value->allowsNull() ? '?' : '') . $this->getClassName($value->getName());
					}
				elseif ('ReflectionUnionType' == $class)
					{
					$types = $value->getTypes();
					$value = $bar = '';

					foreach ($types as $type)
						{
						$value .= $bar;
						$bar = '|';
						$value .= $this->getClassName($type->getName());
						}
					}
				elseif ('ReflectionIntersectionType' == $class)
					{
					$types = $value->getTypes();
					$value = $bar = '';

					foreach ($types as $type)
						{
						$value .= $bar;
						$bar = '&';
						$value .= $this->getClassName($type->getName());
						}
					}
				else
					{
					$value = $this->getClassName($class);
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
				$value = $this->getColor('number', (string)$value);
			}

		return $value;
		}

	/**
	 * Format comments without indentation
	 * @template T of \ReflectionClass
	 * @param \ReflectionMethod | \ReflectionClass<T> | null $reflection if \ReflectionClass, then grab the comments from the class header
	 */
	protected function formatComments(?\phpDocumentor\Reflection\DocBlock $docBlock, \ReflectionMethod | \ReflectionClass | null $reflection = null) : string
		{
		if (! $docBlock)
			{
			return '';
			}

		$container = new \PHPFUI\Container();
		$container->add($this->parsedown->text($this->getInheritedText($docBlock, $reflection, 'getSummary')));
		$desc = $this->getInheritedText($docBlock, $reflection);

		if ($desc)
			{
			$div = new \PHPFUI\HTML5Element('div');
			$div->addClass('description');
			$div->add($this->parsedown->text($desc));
			$container->add($div);
			}

		$tags = $docBlock->getTags();
		// if we are in a method, inheritdoc makes sense, and we should get the correct doc block comments
		if ($reflection instanceof \ReflectionMethod)
			{
			$tags = $this->getInheritedDocBlock($tags, $reflection);
			}

		if ($tags)
			{
			$ul = new \PHPFUI\UnorderedList();

			foreach ($tags as $tag)
				{
				$name = $tag->getName();
				$description = \method_exists($tag, 'getDescription') ? \trim($tag->getDescription() ?? '') : '';
				$body = '';
				// punt on useless tags
				if (\in_array($name, ['method', 'inheritdoc']))
					{
					continue;
					}

				if (\method_exists($tag, 'getType'))
					{
					$type = $tag->getType();
					}
				else
					{
					$type = '';
					}

				if ('var' == $name || 'param' == $name)
					{
					// useless if no description or type
					if (! $description && ! $type)
						{
						continue;
						}
					}

				if (\method_exists($tag, 'getAuthorName'))
					{
					// @phpstan-ignore-next-line
					$body .= \PHPFUI\Link::email($tag->getEmail(), $tag->getAuthorName());
					}

				if (\method_exists($tag, 'getReference'))
					{
					$body .= $tag->getReference();
					}

				if (\method_exists($tag, 'getVersion'))
					{
					$body .= $tag->getVersion();
					}

				if (\method_exists($tag, 'getLink'))
					{
					$body .= new \PHPFUI\Link($tag->getLink(), '', false);
					}

				if ($type)
					{
					$body .= $this->getClassName($type) . ' ';
					}

				if (\method_exists($tag, 'getVariableName'))
					{
					$varname = $tag->getVariableName();

					if ($varname)
						{
						$body .= $this->getColor('variable', '$' . $varname) . ' ';
						}
					}
				$body .= $this->parsedown->html(\str_replace(['<', '>'], ['&lt;', '&gt;'], $description));
				$ul->addItem(new \PHPFUI\ListItem($this->getColor('name', $name) . ' ' . $this->getColor('description', $body)));
				}

			$attributes = $this->getAttributes($reflection);

			foreach ($attributes as $attribute)
				{
				$ul->addItem(new \PHPFUI\ListItem($this->getColor('name', 'attribute') . ' ' . $this->formatAttribute($attribute)));
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
			$parts = \explode('|', $class);

			if (\count($parts) > 1)
				{
				$returnValue = [];

				foreach ($parts as $part)
					{
					$returnValue[] = $this->getClassName($part, true);
					}

				return \implode('|', $returnValue);
				}


				if ('\\' == $class[0])
					{
					$class = \substr($class, 1);
					}

				if (\str_contains($class, '[]'))
					{
					$array = '[]';
					$class = \str_replace($array, '', $class);
					}
				// if fully qualified, we are done
				if (\PHPFUI\InstaDoc\NamespaceTree::hasClass($class))
					{
					return new \PHPFUI\Link($this->controller->getClassUrl($class), \str_replace('\\', '<wbr>\\', $class), false) . $array;
					}

				// try name in current namespace tree
				$namespacedClass = $this->reflection->getNamespaceName() . '\\' . $class;

				if (\PHPFUI\InstaDoc\NamespaceTree::hasClass($namespacedClass))
					{
					return new \PHPFUI\Link($this->controller->getClassUrl($namespacedClass), \str_replace('\\', '<wbr>\\', $namespacedClass), false) . $array;
					}

			}

		return $this->getColor('type', \htmlspecialchars($class)) . $array;
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
	protected function getComments(?\phpDocumentor\Reflection\DocBlock $docBlock, ?\ReflectionMethod $reflection = null) : string
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
		$cell11->add($this->formatComments($docBlock, $reflection));
		$gridX->add($cell11);

		return $gridX;
		}

	protected function getDocBlock(object $method) : ?\phpDocumentor\Reflection\DocBlock
		{
		$comments = $method->getDocComment();

		if (! $comments)
			{
			return null;
			}

		$comments = \str_ireplace('inheritdocs', 'inheritdoc', $comments);
		$comments = \str_ireplace('{@inheritdoc}', '@inheritdoc', $comments);

		try
			{
			$docBlock = $this->factory->create($comments);
			}
		catch (\Exception)
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
		return \str_replace('\\', '-', $class);
		}

	/**
	 * @template T of \ReflectionClass
	 * @param \ReflectionMethod | \ReflectionClass<T> | null $reflection if \ReflectionClass, then grab the comments from the class header
	 */
	protected function getInheritedText(\phpDocumentor\Reflection\DocBlock $docBlock, \ReflectionMethod | \ReflectionClass | null $reflection = null, string $textType = 'getDescription') : string
		{
		$summary = $docBlock->{$textType}();

		if (! $reflection || 'getSummary' == $textType)
			{
			return $summary;
			}

		$tags = $docBlock->getTags();

		foreach ($tags as $index => $tag)
			{
			if (false !== \stripos($tag->getName(), 'inheritdoc'))
				{
				if ($reflection instanceof \ReflectionMethod)
					{
					$reflectionClass = $reflection->getDeclaringClass();
					$parent = $reflectionClass->getParentClass();

					while ($parent)
						{
						try
							{
							$method = $parent->getMethod($reflection->name);
							}
						catch (\Throwable)
							{
							$method = null;
							}

						if ($method)
							{
							$docBlock = $this->getDocBlock($method);

							if ($docBlock)
								{
								return $this->getInheritedText($docBlock, $method) . $summary;
								}
							}
						$parent = $parent->getParentClass();
						}

					break;
					}


					$parent = $reflection->getParentClass();

					while ($parent)
						{
						$comments = $parent->getDocComment();

						if ($comments)
							{
							$comments = \str_replace('{@inheritdoc}', '@inheritdoc', $comments);
							$docblock = $this->factory->create($comments);
							$summary = $this->formatComments($docblock, $parent) . $summary;
							}
						$parent = $parent->getParentClass();
						}

				break;
				}
			}

		return $summary;
		}

	/**
	 * @param array<int, \phpDocumentor\Reflection\DocBlock\Tag> $tags
	 * @template T of \ReflectionClass
	 * @param \ReflectionMethod | \ReflectionClass<T> | null $reflection if \ReflectionClass, then grab the comments from the class header
	 *
	 * @return array<int, \phpDocumentor\Reflection\DocBlock\Tag>
	 */
	protected function getInheritedDocBlock(array $tags, \ReflectionMethod | \ReflectionClass | null $reflection) : array
		{
		foreach ($tags as $index => $tag)
			{
			if (false !== \stripos($tag->getName(), 'inheritdoc'))
				{
				$reflectionClass = ($reflection instanceof \ReflectionMethod) ? $reflection->getDeclaringClass() : $reflection;
				$parent = $reflectionClass->getParentClass();

				while ($parent)
					{
					try
						{
						$method = $parent->getMethod($reflection->name);
						}
					catch (\Throwable)
						{
						$method = null;
						}

					if ($method)
						{
						$docBlock = $this->getDocBlock($method);

						if ($docBlock)
							{
							// add in the new tags and check parent
							\array_splice($tags, $index, 1, $docBlock->getTags());

							return $this->getInheritedDocBlock($tags, $method);
							}
						}
					$parent = $parent->getParentClass();
					}

				break;
				}
			}

		return $tags;
		}

	/**
	 * @return array<string, string>
	 */
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
			$description = \method_exists($tag, 'getDescription') ? \trim($tag->getDescription() ?? '') : '';

			if ('param' == $name && $description)
				{
				// @phpstan-ignore-next-line
				$var = $tag->getVariableName();
				$comments[$var] = $this->parsedown->html($description);
				}
			}

		return $comments;
		}

	protected function getMethodParametersBlock(\ReflectionFunction|\ReflectionMethod $method) : string
		{
		$docBlock = $this->getDocBlock($method);
		$parameterComments = $this->getParameterComments($docBlock);
		$info = '(' . $this->getMethodParameters($method, $parameterComments) . ')';

		if ($method->hasReturnType())
			{
			$info .= ' : ' . $this->getValueString($method->getReturnType());
			}

		$info .= $this->getComments($docBlock, $method instanceof \ReflectionMethod ? $method : null);

		return $info;
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

	/**
	 * @return array<int, \ReflectionAttribute<object>>
	 */
	protected function getAttributes(?object $reflection) : array
		{
		if ($reflection && \method_exists($reflection, 'getAttributes'))
			{
			return $reflection->getAttributes();
			}

		return [];
		}

	/**
	 * @param \ReflectionAttribute<object> $attribute
	 */
	protected function formatAttribute(\ReflectionAttribute $attribute) : string
		{
		$parameters = '';
		$arguments = $attribute->getArguments();

		if ($arguments)
			{
			$parameters = ' (';
			$comma = '';

			foreach ($arguments as $name => $argument)
				{
				$name = \is_int($name) ? '' : $this->getAttributeName($name) . ': ';

				if (\is_string($argument))
					{
					$link = $this->getAttributeName($argument, true);
					}
				else
					{
					$link = $this->getValueString($argument);
					}
				$parameters .= "{$comma} {$name}{$link}";

				$comma = ', ';
				}
			$parameters .= ')';
			}

		$targeting = '';

		return $this->getClassName($attribute->getName()) . $parameters . $targeting;
		}

	private function getAttributeName(string $name, bool $asValue = false) : string
		{
		$link = $this->getClassName($name);

		if (\strpos($link, 'href='))
			{
			$name = $link;
			}
		elseif ($asValue)
			{
			$name = $this->getValueString($name);
			}

		return $name;
		}
	}
