<?php

namespace PHPFUI\InstaDoc\Section;

class File extends \PHPFUI\InstaDoc\Section
	{

	public function generate(\PHPFUI\Page $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$fullClassPath = str_replace('\\', '/', $fullClassPath);
		if (! file_exists($fullClassPath))
			{
			$fullClassPath = $this->controller->getFileManager()->getComposerPath() . $fullClassPath;
			}
		$parameters = $this->controller->getParameters();

		$page->addStyleSheet("highlighter/styles/{$parameters['CSS']}.css");
		$page->addCSS("code{tab-size:{$parameters['t']};-moz-tab-size:{$parameters['t']}}");
		$hl = new \Highlight\Highlighter();
		$php = file_get_contents($fullClassPath);

		// Highlight some code.
		$highlighted = $hl->highlight('php', $php);
		$pre = new \PHPFUI\HTML5Element('pre');
		$code = new \PHPFUI\HTML5Element('code');
		$code->addClass('hljs');
		$code->addClass($highlighted->language);
		$code->add($highlighted->value);
		$pre->add($code);
		$container->add($pre);

		return $container;
		}
	}
