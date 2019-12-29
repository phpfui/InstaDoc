<?php

namespace PHPFUI\InstaDoc\Section;

class Doc extends \PHPFUI\InstaDoc\Section
	{

	public function generate(\PHPFUI\Page $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$container->add(new \PHPFUI\SubHeader('Docs'));
		$container->add(new \PHPFUI\SubHeader($fullClassPath));

		return $container;
		}
	}
