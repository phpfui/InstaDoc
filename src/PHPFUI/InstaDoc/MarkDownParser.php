<?php

namespace PHPFUI\InstaDoc;

class MarkDownParser
	{
	private \cebe\markdown\GithubMarkdown $parser;

	private \PHPFUI\Interfaces\Page $page;

	public function __construct(\PHPFUI\Interfaces\Page $page)
		{
		$this->parser = new \cebe\markdown\GithubMarkdown();
		$this->page = $page;
		$this->parser->html5 = true;
		$this->parser->keepListStartNumber = true;
		$this->parser->enableNewlines = true;
		}

	public function fileText(string $filename) : string
		{
		$markdown = @\file_get_contents($filename);

		return $this->text($markdown);
		}

	public function text(string $markdown) : string
		{
		$position = 0;
		$hl = new \Highlight\Highlighter();

		$div = new \PHPFUI\HTML5Element('div');
		$div->addClass('markdown-body');
		$html = $this->parser->parse($markdown);
		$dom = new \voku\helper\HtmlDomParser($html);
		$codeBlocks = $dom->find('.language-PHP');

		foreach ($codeBlocks as $block)
			{
			$child = $block->firstChild();
			$highlighted = $hl->highlight('php', $child->text());
			$block->setAttribute('class', 'hljs ' . $highlighted->language);
			$block->parentNode()->setAttribute('class', 'hljs ' . $highlighted->language);
			$child->plainText = \htmlspecialchars_decode($highlighted->value);
			}
		$div->add("{$dom}");

		return $div;
		}
	}
