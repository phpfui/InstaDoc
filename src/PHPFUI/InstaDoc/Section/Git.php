<?php

namespace PHPFUI\InstaDoc\Section;

class Git extends \PHPFUI\InstaDoc\Section
	{

	public function generate(\PHPFUI\Page $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$repo = new \Gitonomy\Git\Repository($_SERVER['DOCUMENT_ROOT'] . '/..');
		$tree = $repo->getHeadCommit()->getTree();
		$fullClassPath = substr(str_replace('\\', '/', $fullClassPath), 3);
		$source = $tree->resolvePath($fullClassPath);
		$entries = $source->getEntries();
		$pre = new \PHPFUI\HTML5Element('pre');
		$pre->add($fullClassPath);
		$pre->add(print_r($entries, true));
		$container->add($pre);

		return $container;
		}
	}
