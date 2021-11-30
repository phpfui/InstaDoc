<?php

namespace PHPFUI\InstaDoc\Tests;

/**
 * A test class with no functionality.
 *
 * It is just to test InstaDoc
 *
 * @author bruce (11/27/2021)
 */
class Test81
	{
	private readonly Status $status;

	public function __construct(private readonly ?Status $enum = Status::Published)
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
	}
