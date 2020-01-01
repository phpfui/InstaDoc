<?php

namespace PHPFUI\InstaDoc;

interface PageInterface
	{

	public function __construct();
	public function __toString() : string;
	public function addBody($item) : Page;
	public function create(\PHPFUI\Menu $menu) : void;
	public function setGenerating(string $generating) : Page;
	public function setParameters(array $parameters) : Page;

	}
