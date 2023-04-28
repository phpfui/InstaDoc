<?php

namespace PHPFUI\InstaDoc;

class MarkDownParser
	{
	private \cebe\markdown\GithubMarkdown $parser;

	public function __construct()
		{
		$this->parser = new \cebe\markdown\GithubMarkdown();
		$this->parser->html5 = true;
		$this->parser->keepListStartNumber = true;
		$this->parser->enableNewlines = true;
		}

	public function fileText(string $filename) : string
		{
		$markdown = \file_exists($filename) ? \file_get_contents($filename) : '';

		return $this->text($markdown);
		}

	public function html(string $markdown) : string
		{
		return $this->parser->parseParagraph(\str_replace(['<p>', '</p>'], '', $markdown));
		}

	public function text(string $markdown) : string
		{
		$position = 0;
		$hl = new \Highlight\Highlighter();

		$div = new \PHPFUI\HTML5Element('div');
		$div->addClass('markdown-body');
		$html = $this->parser->parse($markdown);
		$dom = new \voku\helper\HtmlDomParser($html);
		$codeBlocks = $dom->find('.language-php');

		foreach ($codeBlocks as $block)
			{
			$child = $block->firstChild();
			$highlighted = $hl->highlight('php', $child->text());
			$block->setAttribute('class', 'hljs ' . $highlighted->language);
			$block->parentNode()->setAttribute('class', 'hljs ' . $highlighted->language);
			$child->plaintext = \htmlspecialchars_decode($highlighted->value);
			}
		$div->add("{$dom}");

		return $div;
		}
	}
