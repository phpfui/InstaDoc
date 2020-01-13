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
	public const CONST_PUBLIC_STRING = 'Default';
	protected const CONST_PROTECTED_INT = 42;
	private const CONST_PRIVATE_ARRAY = ['.Git', 0, true, 0.2, ];
	public $public_float = 3.14;
	protected $protected_string = 'whatever';

	private $private_array = ['fred', 1, false, 9.9, ['nested', self::CONST_PRIVATE_ARRAY]];

	public function public_function_returning_and_taking_array(array $array = ['tom', 2 => 'Dick', 'harry' => 'reasoner']) : array
		{
		return [];
		}

	/**
	 * This function does nothing. But it has a very long
	 * meaningless description that just seems to go on and on and
	 * on but does not really say anything except for being very
	 * long and completely unreadable, but such is the nature of
	 * long meaningless comments that really say nothing of any
	 * importance that just seem to meander and never get to the
	 * point and be concise and to the point, but that is the point,
	 * that there is no point. Pointless really.....
	 *
	 * @param string $fred nothing to note here
	 * @param $unknown $unknown is pi
	 */
	protected function protected_function_no_return(string $fred, $unknown = 3.14) : void {}

	/**
	 * This function does nothing.
	 */
	private function private_function_no_return(string $fred = 'Eythel') : void {}

	}
