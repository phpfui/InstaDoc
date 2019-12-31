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
class SectionTest extends \PHPFUI\HTMLUnitTester\Extensions
	{

	private $sections = [];
	private $fileManager;
	private $controller;

	/**
	 * Get all sections and save for later use
	 */
	public function setUp() : void
		{
		$rdi = new \RecursiveDirectoryIterator('src/PHPFUI/InstaDoc/Section');
		$iterator = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($iterator as $filename => $fileInfo)
			{
			if (! $fileInfo->isDir())
				{
				$path = str_replace('/', '\\', str_replace('.php', '', $filename));
				$parts = explode('\\', $path);
				while (count($parts) && array_shift($parts) != 'src')
					{
					// leave part on floor, not needed
					}
				$this->sections[] = implode('\\', $parts);
				}
			}

		$this->fileManager = new \PHPFUI\InstaDoc\FileManager('./');
		$this->fileManager->load();
		$this->controller = new \PHPFUI\InstaDoc\Controller($this->fileManager);
		}

	public function testHaveSections() : void
		{
		$this->assertNotEmpty($this->sections, 'No PHPFUI\InstaDoc\Section classes found');
		}

	public function testSectionsGenerateValidHTML() : void
		{
		$page = new \PHPFUI\Page();

		foreach ($this->sections as $section)
			{
			$sectionObject = new $section($this->controller);
			$container = $sectionObject->generate($page, 'src/' . $section . '.php');
			$this->assertValidHtml("{$container}");
			}
		}

	}

