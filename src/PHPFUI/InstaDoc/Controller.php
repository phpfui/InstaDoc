<?php

namespace PHPFUI\InstaDoc;

class Controller
	{
	// pages
	public const DOC_PAGE = 'd';
	public const FILE_PAGE = 'f';
	public const GIT_PAGE = 'g';

	// parameters
	public const CLASS_NAME = 'c';
	public const CSS_FILE = 'CSS';
	public const NAMESPACE = 'n';
	public const PAGE = 'p';
	public const TAB_SIZE = 't';

	// allowed page sections
	private const SECTIONS = ['git', 'file', 'doc', 'landing', 'home'];
	private const VALID_PARAMETERS = [
		Controller::NAMESPACE => '',
		Controller::CLASS_NAME => '',
		Controller::TAB_SIZE => '',
		Controller::CSS_FILE => '',
		Controller::PAGE => '',
		];

	// valid static page parameters
	private const VALID_STATIC_PARTS = [Controller::NAMESPACE, Controller::CLASS_NAME, Controller::PAGE, ];

	private $accordionMenu = null;
	private $currentPage = Controller::DOC_PAGE;
	private $fileManager;
	private $homePageMarkdown = [];

	private $page;
	private $requestedClass = '';
	private $requestedCSS = 'qtcreator_dark';
	private $requestedNamespace = '';
	private $requestedTs = 2;
	private $siteTitle = 'PHPFUI/InstaDoc';
	private $generating = '';

	public function __construct(FileManager $fileManager)
		{
		$this->fileManager = $fileManager;
		$this->page = new Page();
		\PHPFUI\Page::setDebug(1);

		foreach ($this->page->getQueryParameters() as $parameter => $value)
			{
			$this->setParameter($parameter, $value);
			}
		}

	public function addHomePageMarkdown(string $path) : Controller
		{
		$this->homePageMarkdown[$path] = true;

		return $this;
		}

	public function display() : string
		{
		$page = $this->getPage();
		$page->setGenerating($this->generating);
		$page->setParameters($this->getParameters());
		$page->create($this->getMenu());
		$mainColumn = new \PHPFUI\Container();

		if (! $this->requestedClass && $this->requestedNamespace)
			{
			$mainColumn->add($this->getSection('landing')->generate($page, $this->requestedNamespace));
			}
		elseif ($this->requestedClass && $this->requestedNamespace)
			{
			$fullClassName = $this->requestedNamespace . '\\' . $this->requestedClass;
			$tree = NamespaceTree::findNamespace($this->requestedNamespace);
			$files = $tree->getClassFilenames();
			$fullClassPath = $files[$fullClassName] ?? '';
			$section = new Section($this);
			$mainColumn->add($section->getBreadCrumbs($fullClassName));
			$mainColumn->add($section->getMenu($fullClassName));

			if (Controller::DOC_PAGE == $this->currentPage)
				{
				$mainColumn->add($this->getSection('doc')->generate($page, $fullClassPath));
				}
			elseif (Controller::GIT_PAGE == $this->currentPage)
				{
				$mainColumn->add($this->getSection('git')->generate($page, $fullClassPath));
				}
			elseif (Controller::FILE_PAGE == $this->currentPage)
				{
				$mainColumn->add($this->getSection('file')->generate($page, $fullClassPath));
				}
			else
				{
				$mainColumn->add($this->getSection('doc')->generate($page, $fullClassPath));
				}
			}
		else
			{
			$mainColumn->add($this->getSection('home')->generate($page, ''));
			}
		$page->addBody($mainColumn);

		return "{$page}";
		}

	/**
	 * Generate static files for high volume sites.  Pass the path to the directory where you want the files to be placed, it must exist.
	 */
	public function generate(string $directoryPath, array $pagesToInclude = [Controller::DOC_PAGE], string $extension = '.html') : Controller
		{
		if (! file_exists($directoryPath))
			{
			throw new \Exception("The directory {$directoryPath} does not exist");
			}
		$this->generating = $extension;
		$directoryPath .= '/';

		$directoryPath = str_replace('//', '/', $directoryPath);

		// add in the index file
		file_put_contents($directoryPath . 'index' . $extension, $this->display());

		$namespaces = [];

		// loop through all classes and generate all requested pages and namespaces
		foreach (NamespaceTree::getAllClasses() as $path => $class)
			{
			$parameters = $this->getClassParts($class);
			$namespaces[$parameters[Controller::NAMESPACE]] = true;
			foreach ($pagesToInclude as $page)
				{
				$parameters[Controller::PAGE] = $page;
				$this->setParameters($parameters);
				file_put_contents($directoryPath . $this->getUrl($parameters), $this->display());
				}
			}

		$parameters = [];
		foreach ($namespaces as $namespace => $value)
			{
			$parameters[Controller::NAMESPACE] = $namespace;
			file_put_contents($directoryPath .  $this->getUrl($parameters), $this->display());
		}

		$this->generating = '';

		return $this;
		}

	public function getClassParts(string $namespacedClass) : array
		{
		$parts = explode('\\', $namespacedClass);
		$namespace = '';
		$backSlash = '';
		while (count($parts) > 1)
			{
			$namespace .= $backSlash . array_shift($parts);
			$backSlash = '\\';
			}
		$class = $parts[0];

		$parameters = [
			Controller::NAMESPACE => $namespace,
			Controller::CLASS_NAME => $class,
			];

		return $parameters;
		}

	public function getClassURL(string $namespacedClass) : string
		{
		$url = $this->getUrl([Controller::PAGE => Controller::DOC_PAGE] + $this->getClassParts($namespacedClass) + $this->getParameters());

		return $url;
		}

	public function getFileManager() : FileManager
		{
		return $this->fileManager;
		}

	public function getHomePageMarkdown() : array
		{
		return array_keys($this->homePageMarkdown);
		}

	public function getLandingPageUrl(string $namespace) : string
		{
		$parameters = $this->getParameters();

		$parameters[Controller::NAMESPACE] = $namespace;
		unset($parameters[Controller::CLASS_NAME], $parameters[Controller::PAGE]);

		$url = $this->getUrl($parameters);

		return $url;
		}

	public function getMenu() : \PHPFUI\AccordionMenu
		{
		// cache if not generating static docs
		if (! $this->generating && $this->accordionMenu)
			{
			return $this->accordionMenu;
			}

		NamespaceTree::setActiveClass($this->requestedClass);
		NamespaceTree::setActiveNamespace($this->requestedNamespace);
		NamespaceTree::setController($this);
		$this->accordionMenu = new \PHPFUI\AccordionMenu();
		NamespaceTree::populateMenu($this->accordionMenu);

		return $this->accordionMenu;
		}

	public function getPage() : PageInterface
		{
		$page = new Page();
		$page->setPageName($this->siteTitle);

		return $page;
		}

	public function getPageURL(string $page) : string
		{
		$parameters = $this->getParameters();

		$parameters[Controller::PAGE] = $page;
		$url = $this->getUrl($parameters);

		return $url;
		}

	public function getParameters() : array
		{
		$parameters = [
			Controller::NAMESPACE => $this->requestedNamespace,
			Controller::CLASS_NAME => $this->requestedClass,
			Controller::TAB_SIZE => $this->requestedTs,
			Controller::CSS_FILE => $this->requestedCSS,
			Controller::PAGE => $this->currentPage,
			];

		return $parameters;
		}

	public function getSection(string $sectionName) : Section
		{
		if (! in_array($sectionName, Controller::SECTIONS))
			{
			throw new \Exception("{$sectionName} is not one of " . implode(', ', $sections));
			}

		$class = 'PHPFUI\\InstaDoc\\Section\\' . ucfirst($sectionName);

		return new $class($this);
		}

	public function getUrl(array $parameters) : string
		{
		if (! $this->generating)
			{
			$url = $this->page->getBaseUrl() . '?' . http_build_query($parameters);

			return $url;
			}

		$parts = [];
		foreach (Controller::VALID_STATIC_PARTS as $part)
			{
			if (isset($parameters[$part]))
				{
				$parts[] = str_replace('\\', '_', $parameters[$part]);
				}
			}

		$url = implode('_', $parts) . $this->generating;

		return $url;
		}

	public function setPageTitle(string $title) : Controller
		{
		$this->siteTitle = $title;

		return $this;
		}

	public function setParameter(string $parameter, string $value) : Controller
		{
		switch ($parameter)
			{
			case Controller::NAMESPACE:
				$this->requestedNamespace = $value;

				break;

			case Controller::CLASS_NAME:
				$this->requestedClass = $value;

				break;

			case Controller::TAB_SIZE:
				$this->requestedTs = $value;

				break;

			case Controller::CSS_FILE:
				$this->requestedCSS = $value;

				break;

			case Controller::PAGE:
				$this->currentPage = $value;

				break;
			}

		return $this;
		}

	public function setParameters(array $parameters) : Controller
		{
		foreach ($parameters as $key => $value)
			{
			$this->setParameter($key, $value);
			}

		return $this;
		}

	}
