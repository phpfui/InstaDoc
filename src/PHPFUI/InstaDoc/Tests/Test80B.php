<?php

namespace PHPFUI\InstaDoc\Tests;

/**
 * A test class with no functionality.
 *
 * It is just to test InstaDoc
 *
 * @author bruce (1/3/2020)
 */
#[Test80]
#[\PHPFUI\InstaDoc\Tests\Test80]
#[\PHPFUI\InstaDoc\Tests\Test80(1234), \PHPFUI\InstaDoc\Tests\Test80(value: 1234), \PHPFUI\InstaDoc\Tests\Test80(['key' => 'value'])]
#[\PHPFUI\InstaDoc\Tests\Test80(\PHPFUI\InstaDoc\Tests\Test80::CONST_PUBLIC_STRING)]
#[\PHPFUI\InstaDoc\Tests\Test80(100 + 200)]
#[Property(type: 'function', name: 'Hello')]
#[Listens(\PHPFUI\Page::class)]
#[Route(\PHPFUI\InstaDoc\Controller::CLASS_NAME, '/products/create')]
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
