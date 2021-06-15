<?php

/**
 * This file is part of the PHPFUI package
 *
 * (c) Bruce Wells
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source
 * code
 */
class CSSTest extends \PHPFUI\HTMLUnitTester\Extensions
	{
	public function testValidCSS() : void
		{
		$this->assertDirectory('ValidCSS', __DIR__ . '/../css');
		}

	public function testWarningCSS() : void
		{
		$this->assertDirectory('NotWarningCSS', __DIR__ . '/../css');
		}
	}
