<?php

class SyntaxTest extends \PHPFUI\PHPUnitSyntaxCoverage\Extensions
	{

	public function testDirectory() : void
		{
		$this->addSkipDirectory(str_replace('/', DIRECTORY_SEPARATOR, 'PHPFUI/InstaDoc/Tests'));
		$this->assertValidPHPDirectory(__DIR__ . '/../src', 'src directory has an error');
		}

	public function testValidPHPFile() : void
		{
		$this->assertValidPHPFile(__DIR__ . '/../install.php', 'install file is bad');
		}

	}
