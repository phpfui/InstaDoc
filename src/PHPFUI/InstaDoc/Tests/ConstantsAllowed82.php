<?php

namespace PHPFUI\InstaDoc\Tests;

trait ConstantsAllowed82
	{
	public const CONSTANT = 1;

	public function bar() : int
	{
		return Foo::CONSTANT;
	}
	}
