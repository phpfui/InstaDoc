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
		// give us easier to debug line numbers
		\PHPFUI\Page::setDebug(1);

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
		$this->fileManager->addNamespace('PHPFUI', './src', true);
		$this->fileManager->rescan();
		$this->controller = new \PHPFUI\InstaDoc\Controller($this->fileManager);
		}

	public function testHaveSections() : void
		{
		$this->assertNotEmpty($this->sections, 'No PHPFUI\InstaDoc\Section classes found');
		}

	public function testSectionsGenerateValidHTML() : void
		{
		$page = new \PHPFUI\InstaDoc\Page($this->controller);

		foreach ($this->sections as $section)
			{
			$sectionObject = new $section($this->controller);
			$container = $sectionObject->generate($page, $section . '.php');
			$this->assertValidHtml("{$container}");
			}
		}

	public function testClassesGenerateValidHTML() : void
		{
		foreach ($this->sections as $section)
			{
			$this->controller->setParameters($this->controller->getClassParts($section));

			foreach ([\PHPFUI\InstaDoc\Controller::DOC_PAGE, \PHPFUI\InstaDoc\Controller::FILE_PAGE, \PHPFUI\InstaDoc\Controller::GIT_PAGE] as $page)
				{
				$this->controller->setParameter(\PHPFUI\InstaDoc\Controller::PAGE, $page);
				$page = $this->controller->display();
				$this->assertValidHtml("{$page}");
				$this->assertNotWarningHtml("{$page}");
				}

			// should just display landing page
			$this->controller->setParameter(\PHPFUI\InstaDoc\Controller::PAGE, '');
			$this->controller->setParameter(\PHPFUI\InstaDoc\Controller::CLASS_NAME, '');
			$page = $this->controller->display();
			$this->assertValidHtml("{$page}");
			$this->assertNotWarningHtml("{$page}");
			}
		}

	public function testHomePage() : void
		{
		// should just display home page
		$this->controller->setParameters([]);
		$page = $this->controller->display();
		$this->assertValidHtml("{$page}");
		$this->assertNotWarningHtml("{$page}");
		}

	public function testInvalidPage() : void
		{
		$this->controller->setParameters($this->controller->getClassParts('\\Fred\\Flintstone\\Bedrock'));
		$page = $this->controller->display();
		$this->assertValidHtml("{$page}");
		$this->assertNotWarningHtml("{$page}");
		}

	public function testTestClass() : void
		{
		$this->controller->setParameters($this->controller->getClassParts('\\PHPFUI\\InstaDoc\\TestClass'));
		$page = $this->controller->display();
		$this->assertValidHtml("{$page}");
		$this->assertNotWarningHtml("{$page}");
		}

	}

