<?php

namespace PHPFUI\InstaDoc\Tests;

/**
 * A test class with no functionality.
 *
 * <b>It is just to test InstaDoc</b>
 *
 * @author bruce (12/22/2022)
 */
readonly class Test82
	{
	private Status $status;

	public function __construct(private ?Status $enum = Status::Published)
		{
		}

	public function disjunctiveNormalFormTypes((ConstantsAllowed82 & Status) | null $post) : void
		{
		}

	public function takeAndReturnEnum(?Status $enum = null) : Status
		{
		return $enum;
		}

	final public function intersectionTypesFinal(\Iterator & \Countable $collection) : never
		{
		exit;
		}

	protected function alwaysTrue() : true
		{
		return true;
		}

	protected function alwaysNull() : null
		{
		return null;
		}

	private function alwaysFalse() : false
		{
	return false;
		}
	}
