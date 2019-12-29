<?php

namespace PHPFUI\InstaDoc\Section;

class Home extends \PHPFUI\InstaDoc\Section
	{

	public function generate(\PHPFUI\Page $page, string $fullClassPath) : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$parsedown = new \Parsedown();

		foreach ($this->controller->getHomePageMarkdown() as $file)
			{
			$md = file_get_contents($file);
			$container->add($parsedown->text($md));
			}
		$accordion = new \PHPFUI\Accordion();
		$accordion->addAttribute('data-allow-all-closed', 'true');
		$container->add(new \PHPFUI\SubHeader('Package Documentation'));
		$libraries = $this->controller->getFileManager()->getAllNamespaceDirectories(false);

		foreach ($libraries as $namespace => $value)
			{
			$files = $this->controller->getFileManager()->getFilesInRepository($namespace, '.md');

			foreach ($files as $file)
				{
				if (stripos($file, 'readme.md'))
					{
					$md = file_get_contents($file);
					$accordion->addTab($namespace . ' Readme', $parsedown->text($md));
					}
				}
			}
		$container->add($accordion);

		return $container;
		}
	}
