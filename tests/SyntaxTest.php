<?php

class SyntaxTest extends \PHPFUI\PHPUnitSyntaxCoverage\Extensions
	{

	public function testDirectory() : void
		{
		$this->assertValidPHPDirectory(__DIR__ . '/../src', 'src directory has an error');
		}

	public function testValidPHPFile() : void
		{
		$this->assertValidPHPFile(__DIR__ . '/../install.php', 'install file is bad');
		$this->assertValidPHPFile(__DIR__ . '/../commonbase.php', 'commonbase file is bad');
		}

	}
