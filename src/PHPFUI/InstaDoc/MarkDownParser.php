<?php

namespace PHPFUI\InstaDoc;

class MarkDownParser
	{
	private \League\CommonMark\GithubFlavoredMarkdownConverter $parser;

	public function __construct()
		{
		$this->parser = new \League\CommonMark\GithubFlavoredMarkdownConverter(['html_input' => 'strip', 'allow_unsafe_links' => false, ]);
		}

	public function fileText(string $filename) : string
		{
		$markdown = \file_exists($filename) ? \file_get_contents($filename) : '';

		return $this->text($markdown);
		}

	public function html(string $markdown) : string
		{
		$markdown = \str_replace('<?php', '', $markdown);
		$html = $this->parser->convert($markdown);

		return \str_replace(['<p>', '</p>'], '', "{$html}");
		}

	public function text(string $markdown) : string
		{
		$markdown = \str_replace('<?php', '', $markdown);
		$hl = new \Highlight\Highlighter();

		$div = new \PHPFUI\HTML5Element('div');
		$div->addClass('markdown-body');
		$html = "{$this->parser->convert($markdown)}";
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

		return "{$div}";
		}
	}
