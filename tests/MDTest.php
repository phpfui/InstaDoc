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
class MDTest extends \PHPFUI\HTMLUnitTester\Extensions
	{
	public function testMarkdownFiles() : void
		{
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../vendor'));

		$parser = new \PHPFUI\InstaDoc\MarkDownParser();

		$ignoreDirectories = ['devbridge', ];

		foreach ($iterator as $file)
			{
			foreach ($ignoreDirectories as $directory)
				{
				if (\str_contains($file->getPathname(), $directory))
					{
					continue 2;
					}
				}
			$fileName = \strtolower($file->getFilename());

			if ($file->isFile() && \str_ends_with($fileName, '.md'))
				{
				$html = $parser->fileText($file->getPathname());
				$this->assertNotWarningHtml($html, "File {$file->getPathname()} has HTML warnings");
				$this->assertValidHtml($html, "File {$file->getPathname()} has HTML errors");
				}
			}
		}
	}
