<?php

namespace PHPFUI\InstaDoc;

class CSSSelector extends \PHPFUI\Input\Select
	{
	public function __construct(\PHPFUI\Page $page, string $current = '')
		{
		parent::__construct('CSS');

		foreach (glob($_SERVER['DOCUMENT_ROOT'] . $page->getResourcePath() . 'highlighter/styles/*.css') as $file)
			{
			$value = substr($file, strrpos($file, '/') + 1);
			$value = substr($value, 0, strlen($value) - 4);
			$name = ucwords(str_replace(['-', '_'], ' ', $value));

			$this->addOption($name, $value, $current == $value);
			}
		}
	}
