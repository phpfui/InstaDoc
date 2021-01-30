<?php

namespace PHPFUI\InstaDoc;

class MarkDownParser
	{
	private $parser;

	public function __construct()
		{
		$this->parser = new \cebe\markdown\GithubMarkdown();
		$this->parser->html5 = true;
		$this->parser->keepListStartNumber = true;
		$this->parser->enableNewlines = true;
		}

	public function fileText(string $filename) : string
		{
		$markdown = @\file_get_contents($filename);

		return $this->parser->parse($markdown);
		}

	public function text(string $markdown) : string
		{
		return $this->parser->parse($markdown);
		}
	}
