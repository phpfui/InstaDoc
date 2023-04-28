<?php

namespace PHPFUI\InstaDoc;

interface PageInterface extends \PHPFUI\Interfaces\Page
	{
	public function __construct(Controller $controller);

	public function __toString() : string;

	public function addBody(mixed $item) : PageInterface;

	public function create(\PHPFUI\Menu $menu) : void;

	public static function setDebug(int $level = 0) : void;

	public function setGenerating(string $generating) : PageInterface;

	public function setHomeUrl(string $url) : PageInterface;
	}
