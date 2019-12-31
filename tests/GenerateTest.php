<?php

/**
 * This file is part of the PHPFUI\InstaDoc package
 *
 * (c) Bruce Wells
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source
 * code
 */
class GenerateTest extends \PHPFUI\HTMLUnitTester\Extensions
	{

	private $fileManager;
	private $controller;

	/**
	 * Get all sections and save for later use
	 */
	public function setUp() : void
		{
		$this->fileManager = new \PHPFUI\InstaDoc\FileManager('./');
		$this->fileManager->rescan();
		$this->controller = new \PHPFUI\InstaDoc\Controller($this->fileManager);
		}

	public function testGenerateFiles() : void
		{
		$directory = 'tests';

		array_map('unlink', glob($directory . '/*.html'));

		$file = $directory . '/index.html';
		$this->assertFileNotExists($file);

		$this->controller->generate('tests', [\PHPFUI\InstaDoc\Controller::DOC_PAGE, \PHPFUI\InstaDoc\Controller::FILE_PAGE, ]);

		$this->assertFileExists($file);

		$this->assertValidFile($file);

		$this->assertGreaterThan(350, count(glob($directory . '/*.html')));

		// be nice and clean up
		array_map('unlink', glob($directory . '/*.html'));
		$this->assertFileNotExists($file);
		}

	}

