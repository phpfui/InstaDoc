<?php

namespace PHPFUI\InstaDoc\Tests;

/**
 * A test class with no functionality.
 *
 * It is just to test InstaDoc
 *
 * @author bruce (1/3/2020)
 */
class Test80B extends Test80A
	{
	/**
	 * @inheritDoc
	 */
	protected function protected_function_no_return(?string $fred, $unknown = 3.14) : void {}

	/**
	 * {@inheritDoc}
	 */
	private function private_function_no_return(string | Test80 | null $fred = 'Eythel') : void {}
	}
