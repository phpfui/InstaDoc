<?php

namespace PHPFUI\InstaDoc\Tests;

/**
 * A test class with no functionality.
 *
 * It is just to test InstaDoc
 *
 * @author bruce (1/3/2020)
 */
class Test80
	{
	public const CONST_PUBLIC_STRING = 'Default';

	protected const CONST_PROTECTED_INT = 42;

	private const CONST_PRIVATE_ARRAY = ['.Git', 0, true, 0.2, ];

	public float $public_float = 3.14;

	protected string $protected_string = 'whatever';

	private array $private_array = ['fred', 1, false, 9.9, ['nested', self::CONST_PRIVATE_ARRAY]];

	public function __construct(public ?Test80 $test = null)
		{
		}

	final public function public_function_returning_and_taking_array(array | bool $array = ['tom', 2 => 'Dick', 'harry' => 'reasoner', ]) : array | bool
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
	 * that there is no point. Pointless really.
	 *
	 * @param string $fred nothing to note here
	 * @param float $unknown is pi
	 *
	 * @api declares that elements are suitable for consumption by third parties.
	 * @example shows the code of a specified example file or, optionally, just a portion of it.
	 * @ignore tells phpDocumentor that the associated element is not to be included in the documentation.
	 * @internal denotes that the associated elements is internal to this application or library and hides it by default.
	 * @source shows the source code of the associated element.
	 * @throws \Fred this is the text
	 * @todo 	indicates whether any development activity should still be executed on the associated element.
	 * @uses 	indicates a reference to (and from) a single associated element.
	 * @var $Properties This the explaination of the var
	 * @return string this is the return text
	 *
	 * @category   CategoryName
	 * @package    PackageName
	 * @author     Original Author <author@example.com>
	 * @author     Another Author <another@example.com>
	 * @copyright  1997-2005 The PHP Group
	 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
	 * @version    SVN: $Id$
	 * @link       http://pear.php.net/package/PackageName
	 * @since      File available since Release 1.2.0
	 * @deprecated File deprecated in Release 2.0.0
	 */
	protected function protected_function_no_return(?string $fred, $unknown = 3.14) : void {}

	/**
	 * This function does nothing.
	 *
	 * @throw //garbage dfsadfsfd
	 */
	private function private_function_no_return(string | Test80 | null $fred = 'Eythel') : void {}

	/**
	 * Testing method sorting
	 */
	private static function theLowerTest() : static { return new static(); }

	/**
	 * Testing method sorting
	 */
	private function UpperCaseMethodName() : mixed { return null; }

	/**
	 * Testing method sorting
	 */
	private function upperTest() : void {}
	}
