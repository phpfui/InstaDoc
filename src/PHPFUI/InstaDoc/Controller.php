<?php

namespace PHPFUI\InstaDoc;

class Controller
	{
	// parameters
	public const CLASS_NAME = 'c';

	public const CSS_FILE = 'CSS';

	public const DOC_PAGE = 'd';

	public const FILE_PAGE = 'f';

	public const GIT_LIMIT = 'gl';

	public const GIT_ONPAGE = 'gp';

	public const GIT_PAGE = 'g';

	public const GIT_SHA1 = 'gs';

	public const NAMESPACE = 'n';

	public const PAGE = 'p';

	public const TAB_SIZE = 't';

	public const VALID_CLASS_PAGES = [
		Controller::DOC_PAGE,
		Controller::FILE_PAGE,
		Controller::GIT_PAGE,
	];

	// allowed page sections
	private const SECTIONS = [
		'Git',
		'File',
		'Doc',
		'Landing',
		'Home',
		'GitDiff',
	];

	private const VALID_PARAMETERS = [
		Controller::NAMESPACE => '',
		Controller::CLASS_NAME => '',
		Controller::TAB_SIZE => '',
		Controller::CSS_FILE => '',
		Controller::PAGE => '',
		Controller::GIT_LIMIT => '',
		Controller::GIT_ONPAGE => '',
		Controller::GIT_SHA1 => '',
	];

	// valid static page parameters
	private const VALID_STATIC_PARTS = [
		Controller::NAMESPACE,
		Controller::CLASS_NAME,
		Controller::PAGE,
	];

	/**
	 * @var array<string> $accessTabs tabs to show access in UI
	 */
	private array $accessTabs = ['Public', 'Protected', 'Private', 'Static'];

	private string $generating = '';

	private string $gitFileOffset = '';

	private string $gitRoot = '';

	/**
	 * @var array<string, bool> markdown file names
	 */
	private array $homePageMarkdown = [];

	private string $homeUrl = '#';

	private ?\PHPFUI\Menu $menu = null;

	private \PHPFUI\InstaDoc\PageInterface $page;

	/**
	 * @var array<string, string>
	 */
	private array $parameters = [];

	private string $siteTitle = 'PHPFUI/InstaDoc';

	public function __construct(private \PHPFUI\InstaDoc\FileManager $fileManager)
		{
		$this->gitRoot = $fileManager->getComposerPath();
		$this->page = $this->getPage();
		$this->setParameters($this->page->getQueryParameters());
		}

	public function addHomePageMarkdown(string $path) : Controller
		{
		$this->homePageMarkdown[$path] = true;

		return $this;
		}

	/**
	 * Clears the cached menu in case you want to use two or more menu types on a page
	 */
	public function clearMenu() : Controller
		{
		$this->menu = null;

		return $this;
		}

	/**
	 * Display a page according to the parameters passed on the url.
	 *
	 * @param array<string> $classPagesToShow limits the allowed pages to display, used for static file generation
	 * @param ?PageInterface $page to use, default to current controller page, but pass in a new page for multiple page generations on the same controller instance
	 */
	public function display(array $classPagesToShow = Controller::VALID_CLASS_PAGES, ?PageInterface $page = null) : string
		{
		if (! $page)
			{
			$page = $this->getControllerPage();
			$page->setPageName($this->siteTitle);
			$page->setHomeUrl($this->homeUrl);
			}
		$page->setGenerating($this->generating);
		$page->create($this->getMenu());
		$mainColumn = new \PHPFUI\Container();

		if (! $this->getParameter(Controller::CLASS_NAME) && $this->getParameter(Controller::NAMESPACE))
			{
			$mainColumn->add($this->getSection('Landing')->generate($page, $this->getParameter(Controller::NAMESPACE)));
			}
		elseif ($this->getParameter(Controller::CLASS_NAME))
			{
			$nameSpace = $this->getParameter(Controller::NAMESPACE);

			if ($nameSpace)
				{
				$nameSpace .= '\\';
				}

			$fullClassName = $nameSpace . $this->getParameter(Controller::CLASS_NAME);
			$tree = NamespaceTree::findNamespace($this->getParameter(Controller::NAMESPACE));
			$files = $tree->getClassFilenames();
			$fullClassPath = $files[$fullClassName] ?? '';

			if ($this->getParameter(Controller::GIT_SHA1))
				{
				return $this->getSection('GitDiff')->generate($page, $fullClassName);
				}
			$section = new Section($this);

			$div = new \PHPFUI\GridX();
			$div->add($section->getBreadCrumbs($fullClassName));

			$cell = new \PHPFUI\Cell();
			$div->add(' &nbsp; ');
			$icon = new \PHPFUI\FAIcon('far', 'clipboard');
			$icon->setToolTip('Send Constructor to Clipboard');
			$callout = new \PHPFUI\Callout('success');
			$callout->add('Copied!');
			$callout->addClass('small');
			$parameters = $this->getConstructorParameters($fullClassName);
			$parameters = \str_replace("\n", '', $parameters);
			// @phpstan-ignore-next-line hack for now
			$page->addCopyToClipboard("new \\{$fullClassName}({$parameters});", $icon, $callout);
			$page->setDebug(1);
			$div->add($callout);
			$mainColumn->add($div);

			$mainColumn->add($section->getMenu($fullClassName, $classPagesToShow));

			if (Controller::DOC_PAGE == $this->getParameter(Controller::PAGE))
				{
				$mainColumn->add($this->getSection('Doc')->generate($page, $fullClassPath));
				}
			elseif (Controller::GIT_PAGE == $this->getParameter(Controller::PAGE))
				{
				$mainColumn->add($this->getSection('Git')->generate($page, $fullClassPath));
				}
			elseif (Controller::FILE_PAGE == $this->getParameter(Controller::PAGE))
				{
				$mainColumn->add($this->getSection('File')->generate($page, $fullClassPath));
				}
			else
				{
				$mainColumn->add($this->getSection('Doc')->generate($page, $fullClassPath));
				}
			}
		else
			{
			$mainColumn->add($this->getSection('Home')->generate($page, ''));
			}
		$page->addBody($mainColumn);

		return "{$page}";
		}

	/**
	 * Generate static files for high volume sites.  Pass the path to the directory where you want the files to be placed, it must exist.
	 *
	 * @param array<string> $pagesToInclude
	 *
	 * @return array<string, float|int<1, max>> array with generation file count and time
	 */
	public function generate(string $directoryPath, array $pagesToInclude = [Controller::DOC_PAGE], string $extension = '.html') : array
		{
		if (! \file_exists($directoryPath))
			{
			throw new \Exception("The directory {$directoryPath} does not exist");
			}
		$count = 1;
		$start = \microtime(true);
		$this->generating = $extension;
		$directoryPath .= '/';

		$directoryPath = \str_replace('//', '/', $directoryPath);

		// add in the index file
		// always create a new page
		$page = $this->getPage();
		$page->setPageName($this->siteTitle);
		$page->setHomeUrl($this->homeUrl);

		\file_put_contents($directoryPath . 'index' . $extension, $this->display($pagesToInclude, $page));

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
				// always create a new page
				$page = $this->getPage();
				$page->setPageName($this->siteTitle);
				$page->setHomeUrl($this->homeUrl);

				\file_put_contents($directoryPath . $this->getUrl($parameters), $this->display($pagesToInclude, $page));
				++$count;
				}
			}

		$parameters = [];

		foreach ($namespaces as $namespace => $value)
			{
			$parameters[Controller::NAMESPACE] = $namespace;
			// always create a new page
			$page = $this->getPage();
			$page->setPageName($this->siteTitle);
			$page->setHomeUrl($this->homeUrl);

			\file_put_contents($directoryPath . $this->getUrl($parameters), $this->display($pagesToInclude, $page));
			++$count;
			}

		$this->generating = '';
		$milliseconds = \microtime(true) - $start;

		return ['count' => $count, 'seconds' => $milliseconds];
		}

	/**
	 * @return array<string> tabs to show access in UI
	 */
	public function getAccessTabs() : array
		{
		return $this->accessTabs;
		}

	/**
	 * break up a namespaced class into parts
	 * @return array<string, string>
	 */
	public function getClassParts(string $namespacedClass) : array
		{
		$parts = \explode('\\', $namespacedClass);
		$namespace = '';
		$backSlash = '';

		while (\count($parts) > 1)
			{
			$namespace .= $backSlash . \array_shift($parts);
			$backSlash = '\\';
			}
		$class = $parts[0];

		$parameters = [
			Controller::NAMESPACE => $namespace,
			Controller::CLASS_NAME => $class,
		];

		return $parameters;
		}

	/**
	 * Get the url given a class
	 */
	public function getClassUrl(string $namespacedClass) : string
		{
		$url = $this->getUrl($this->getClassParts($namespacedClass) + $this->getParameters());

		return $url;
		}

	/**
	 * Returns the current page for the controller that will be used to display the documentation.
	 */
	public function getControllerPage() : PageInterface
		{
		return $this->page;
		}

	public function getFileManager() : FileManager
		{
		return $this->fileManager;
		}

	/**
	 * The git file offset is a relative path to the source to make it compatible with the git repo path.
	 */
	public function getGitFileOffset() : string
		{
		return $this->gitFileOffset;
		}

	/**
	 * The git root is the directory where the associated git repo lives
	 */
	public function getGitRoot() : string
		{
		return $this->gitRoot;
		}

	/**
	 * Get unique home page markdown files
	 *
	 * @return array<string> markdown file names
	 */
	public function getHomePageMarkdown() : array
		{
		return \array_keys($this->homePageMarkdown);
		}

	/**
	 * Return a landing page URL
	 */
	public function getLandingPageUrl(string $namespace) : string
		{
		$parameters = $this->getParameters();

		$parameters[Controller::NAMESPACE] = $namespace;
		unset($parameters[Controller::CLASS_NAME], $parameters[Controller::PAGE]);

		$url = $this->getUrl($parameters);

		return $url;
		}

	/**
	 * Return a menu
	 *
	 * @param \PHPFUI\Menu $menu to use if you don't want the default AccordionMenu
	 */
	public function getMenu(?\PHPFUI\Menu $menu = null) : \PHPFUI\Menu
		{
		// cache if not generating static docs
		if (! $this->generating && $this->menu)
			{
			return $this->menu;
			}

		NamespaceTree::setActiveClass($this->getParameter(Controller::CLASS_NAME));
		NamespaceTree::setActiveNamespace($this->getParameter(Controller::NAMESPACE));
		NamespaceTree::setController($this);

		if (! $menu)
			{
			$menu = new \PHPFUI\AccordionMenu();
			}
		$this->menu = $menu;

		NamespaceTree::populateMenu($this->menu);

		return $this->menu;
		}

	/**
	 * Get the url for a namespace
	 */
	public function getNamespaceURL(string $namespace) : string
		{
		while (\strlen($namespace) && '\\' == $namespace[0])
			{
			$namespace = \substr($namespace, 1);
			}

		$url = $this->getUrl([Controller::PAGE => Controller::DOC_PAGE, Controller::NAMESPACE => $namespace] + $this->getParameters());

		return $url;
		}

	/**
	 * Gets a blank page and sets the page title. Override to change the generated page layout.
	 */
	public function getPage() : \PHPFUI\InstaDoc\PageInterface
		{
		$page = new Page($this);

		return $page;
		}

	/**
	 * Get the url for the specified page
	 */
	public function getPageURL(string $page) : string
		{
		$parameters = $this->getParameters();

		if (! \in_array($page, Controller::VALID_CLASS_PAGES))
			{
			throw new \Exception("Page {$page} is not in " . \implode(', ', Controller::VALID_CLASS_PAGES));
			}

		$parameters[Controller::PAGE] = $page;
		$url = $this->getUrl($parameters);

		return $url;
		}

	/**
	 * Get a specific parameter
	 */
	public function getParameter(string $parameter, ?string $default = null) : string
		{
		if (! isset(Controller::VALID_PARAMETERS[$parameter]))
			{
			throw new \Exception($parameter . ' is an invalid parameter. Valid values: ' . \implode(',', Controller::VALID_PARAMETERS));
			}

		return $this->parameters[$parameter] ?? $default ?? '';
		}

	/**
	 * Get all parameters
	 *
	 * @return array<string, string>
	 */
	public function getParameters() : array
		{
		return $this->parameters;
		}

	/**
	 * Get parameters for the class and method
	 */
	public function getConstructorParameters(string $className) : string
		{
		try
			{
			$reflection = new \ReflectionClass($className);
			}
		catch (\Exception)
			{
			return '';
			}

		$methodName = '__construct';

		if (! $reflection->hasMethod($methodName))
			{
			return '';
			}

		$method = $reflection->getMethod($methodName);
		$section = new \PHPFUI\InstaDoc\Section\CodeCommon($this, $className);
		$parameters = $section->getMethodParameters($method);

		return \Soundasleep\Html2Text::convert($parameters, ['drop_links' => true, 'ignore_errors' => true]);
		}

	/**
	 * Get a section for display. Override to change layout
	 */
	public function getSection(string $sectionName) : Section
		{
		if (! \in_array($sectionName, Controller::SECTIONS))
			{
			throw new \Exception("{$sectionName} is not one of " . \implode(', ', Controller::SECTIONS));
			}

		$class = 'PHPFUI\\InstaDoc\\Section\\' . $sectionName;

		return new $class($this);
		}

	/**
	 * Get a url given parameters.  Remove invalid parameters.
	 *
	 * @param array<string, string> $parameters
	 */
	public function getUrl(array $parameters) : string
		{
		// nuke blank parameters
		foreach ($parameters as $key => $value)
			{
			if (! \strlen($value))
				{
				unset($parameters[$key]);
				}
			}

		if (! $this->generating)
			{
			$url = $this->page->getBaseUrl() . '?' . \http_build_query($parameters);

			return \str_replace('\\', '%5C', $url);
			}

		$parts = [];

		foreach (Controller::VALID_STATIC_PARTS as $part)
			{
			if (isset($parameters[$part]))
				{
				$parts[] = \str_replace('\\', '_', $parameters[$part]);
				}
			}

		$url = \implode('_', $parts) . $this->generating;

		while ('_' == $url[0])
			{
			$url = \substr($url, 1);
			}

		return $url;
		}

	/**
	 * @param array<string> $tabs tabs to show access in UI
	 */
	public function setAccessTabs(array $tabs) : Controller
		{
		$this->accessTabs = $tabs;

		return $this;
		}

	/**
	 * The git file offset is a relative path to the source to make it compatible with the git repo path.
	 */
	public function setGitFileOffset(string $directory) : Controller
		{
		$this->gitFileOffset = $directory;

		return $this;
		}

	/**
	 * This allows InstaDoc to open and display commits from the associated git repo
	 */
	public function setGitRoot(string $directory) : Controller
		{
		$this->gitRoot = $directory;

		if (empty($this->getGitFileOffset()))
			{
			$this->setGitFileOffset($directory);
			}

		return $this;
		}

	/**
	 * Set the home URL for the nav bar menu
	 */
	public function setHomeUrl(string $url) : Controller
		{
		$this->homeUrl = $url;
		$this->page->setHomeUrl($url);

		return $this;
		}

	/**
	 * Set the title for the page
	 */
	public function setPageTitle(string $title) : Controller
		{
		$this->siteTitle = $title;

		return $this;
		}

	/**
	 * Set a parameter, must be valid
	 */
	public function setParameter(string $parameter, string $value) : Controller
		{
		if (! isset(Controller::VALID_PARAMETERS[$parameter]))
			{
			throw new \Exception($parameter . ' is an invalid parameter. Valid values: ' . \implode(',', Controller::VALID_PARAMETERS));
			}
		$this->parameters[$parameter] = $value;

		return $this;
		}

	/**
	 * Set the valid parameters from an array
	 *
	 * @param array<string, string> $parameters key value pairs
	 */
	public function setParameters(array $parameters) : Controller
		{
		$this->parameters = [];

		foreach (Controller::VALID_PARAMETERS as $key => $value)
			{
			if (isset($parameters[$key]) && \strlen($parameters[$key]))
				{
				$this->parameters[$key] = $parameters[$key];
				}
			}

		return $this;
		}
	}
