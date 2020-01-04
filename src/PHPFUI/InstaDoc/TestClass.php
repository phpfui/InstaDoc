<?php

namespace PHPFUI\InstaDoc;

/**
 * A test class with no functionality.
 *
 * It is just to test InstaDoc
 *
 * @author bruce (1/3/2020)
 */
class TestClass
	{
	private const CONST_PRIVATE_ARRAY = ['.Git', 0, true, 0.2, ];
	protected const CONST_PROTECTED_INT = 42;
	public const CONST_PUBLIC_STRING = 'Default';

	private $private_array = ['fred', 1, false, 9.9, ['nested', self::CONST_PRIVATE_ARRAY]];
	protected $protected_string = 'whatever';
	public $public_float = 3.14;

	/**
	 * This function does nothing.
	 */
	private function private_function_no_return(string $fred = 'Eythel') {}

	/**
	 * This function does nothing. But it has a very long
	 * meaningless description that just seems to go on and on and
	 * on but does not really say anything except for being very
	 * long and completely unreadable, but such is the nature of
	 * long meaningless comments that really say nothing of any
	 * importance that just seem to meander and never get to the
	 * point and be concise and to the point, but that is the point,
	 * that there is no point. Pointless really.....
	 */
	protected function protected_function_no_return(string $fred, $unknown = 3.14) {}

	public function public_function_returning_and_taking_array(array $array = ['tom', 2 => 'Dick', 'harry' => "reasoner"]) : array
		{
		return [];
		}

	}
