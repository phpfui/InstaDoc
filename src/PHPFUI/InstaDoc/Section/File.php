<?php

namespace PHPFUI\InstaDoc\Section;

class File extends \PHPFUI\InstaDoc\Section
	{
	public function generate(\PHPFUI\InstaDoc\PageInterface $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$fullClassPath = str_replace('\\', '/', $fullClassPath);

		if (! file_exists($fullClassPath))
			{
			$fullClassPath = $this->controller->getGitFileOffset() . '/' . $fullClassPath;
			}
		$ts = $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::TAB_SIZE, 2);

		$page->addCSS("code{tab-size:{$ts};-moz-tab-size:{$ts}}");
		$php = @file_get_contents($fullClassPath);
		$pre = new \PHPFUI\HTML5Element('pre');

		$css = $this->controller->getParameter(\PHPFUI\InstaDoc\Controller::CSS_FILE, 'qtcreator_dark');

		if ('PHP' != $css)
			{
			$page->addStyleSheet("highlighter/styles/{$css}.css");
			$hl = new \Highlight\Highlighter();

			// Highlight some code.
			$highlighted = $hl->highlight('php', $php);
			$code = new \PHPFUI\HTML5Element('code');
			$code->addClass('hljs');
			$code->addClass($highlighted->language);
			$code->add($highlighted->value);
			$pre->add($code);
			}
		else
			{
			$pre->add(highlight_string($php, true));
			}
		$container->add($pre);

		return $container;
		}
	}
