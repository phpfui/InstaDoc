<?php

namespace PHPFUI\InstaDoc\Tests;

/**
 * A test class with no functionality.
 *
 * <b>It is just to test InstaDoc</b>
 *
 * @author bruce (11/27/2021)
 */
class Test81
	{
	private readonly Status $status;

	public function __construct(private readonly ?Status $enum = Status::Published)
		{
		}

	final public function intersectionTypesFinal(\Iterator & \Countable $collection) : never
		{
		exit;
		}

	public function takeAndReturnEnum(?Status $enum = null) : Status
		{
		return $enum;
		}
	}
