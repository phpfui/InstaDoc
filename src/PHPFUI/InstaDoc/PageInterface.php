<?php

namespace PHPFUI\InstaDoc;

interface PageInterface
	{
	public function __construct(Controller $controller);

	public function __toString() : string;

	public function addBody($item) : PageInterface;

	public function create(\PHPFUI\Menu $menu) : void;

	public function setGenerating(string $generating) : PageInterface;

	public function setHomeUrl(string $url) : PageInterface;
	}
