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

	private const SECTIONS = ['git', 'file', 'doc', 'landing', 'home'];
	private $accordionMenu = null;
	private $currentPage = Controller::DOC_PAGE;
	private $fileManager;
	private $homePageMarkdown = [];

	private $page;
	private $requestedClass = '';
	private $requestedCSS = 'qtcreator_dark';
	private $requestedNamespace = '';
	private $requestedTs = 2;
	private $sections = [];
	private $siteTitle = 'PHPFUI/InstaDoc';

	public function __construct(FileManager $fileManager)
		{
		$this->fileManager = $fileManager;
		$this->page = new Page();
		\PHPFUI\Page::setDebug(1);
		$this->page->addStyleSheet('/css/styles.css');

		$this->sections['git'] = new Section\Git($this);
		$this->sections['file'] = new Section\File($this);
		$this->sections['doc'] = new Section\Doc($this);
		$this->sections['landing'] = new Section\Landing($this);
		$this->sections['home'] = new Section\Home($this);

		$this->parameters = $this->page->getQueryParameters();

		foreach ($this->parameters as $parameter => $value)
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
		$page->setParameters($this->getParameters());
		$page->create($this->siteTitle, $this->getMenu());
		$mainColumn = new \PHPFUI\Container();

		if (! $this->requestedClass && $this->requestedNamespace)
			{
			$mainColumn->add($this->getSection('landing')->generate($page, $this->requestedNamespace));
			}
		elseif ($this->requestedClass && $this->requestedNamespace)
			{
			$fullClassName = $this->requestedNamespace . '\\' . $this->requestedClass;
			$tree = NamespaceTree::getNamespaceTree($fullClassName);
			$fullClassPath = $tree->getPathForClass($this->requestedClass);
			$section = new Section($this);
			$mainColumn->add($section->getBreadCrumbs($fullClassName));
			$mainColumn->add($section->getMenu($this->requestedNamespace));

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

	public function generate(string $path, string $extension = '.html') : Controller
		{
		return $this;
		}

	public function getClassURL(string $namespace, string $class) : string
		{
		$parameters = $this->parameters;

		unset($parameters['submit']);
		$parameters[Controller::NAMESPACE] = $namespace;
		$parameters[Controller::CLASS_NAME] = $class;
		$url = $this->page->getBaseUrl() . '?' . http_build_query($parameters);

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
		$parameters = $this->parameters;

		unset($parameters['submit']);
		$parameters[Controller::NAMESPACE] = $namespace;
		unset($parameters[Controller::CLASS_NAME], $parameters[Controller::PAGE]);

		$url = $this->page->getBaseUrl() . '?' . http_build_query($parameters);

		return $url;
		}

	public function getMenu() : \PHPFUI\AccordionMenu
		{
		if ($this->accordionMenu)
			{
			return $this->accordionMenu;
			}

		foreach ($this->fileManager->getAllNamespaces() as $namespace)
			{
			foreach ($this->fileManager->getClassesInNamespace($namespace) as $file => $class)
				{
				NamespaceTree::getNamespaceTree($namespace . '\\' . $class, $file);
				}
			}

		$iterator = new NamespaceTree();
		$iterator->setActiveClass($this->requestedClass);
		$iterator->setActiveNamespace($this->requestedNamespace);
		$iterator->setBaseUrl($this->page->getBaseURL());
		$this->accordionMenu = new \PHPFUI\AccordionMenu();
		$iterator->populateMenu($this->accordionMenu);

		return $this->accordionMenu;
		}

	public function getPage() : \PHPFUI\Page
		{
		return clone $this->page;
		}

	public function getPageURL(string $page) : string
		{
		$parameters = $this->parameters;

		unset($parameters['submit']);
		$parameters[Controller::PAGE] = $page;
		$url = $this->page->getBaseUrl() . '?' . http_build_query($parameters);

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

		return clone $this->sections[$sectionName];
		}

	public function setPage(\PHPFUI\Page $page) : Controller
		{
		$this->page = $page;

		return $this;
		}

	public function setPageTitle(string $title) : Controller
		{
		$this->siteTitle = $title;

		return $this;
		}

	public function setSection(string $sectionName, Section $section) : Controller
		{
		if (! in_array($sectionName, Controller::SECTIONS))
			{
			throw new \Exception("{$sectionName} is not one of " . implode(', ', $sections));
			}
		$this->sections[$sectionName] = $section;

		return $this;
		}

	}
