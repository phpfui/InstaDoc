<?php

namespace PHPFUI\InstaDoc;

class Page extends \PHPFUI\Page implements PageInterface
	{

	private $mainColumn;
	private $menu;
	private $parameters = [];
	private $generating = '';

	public function __construct()
		{
		parent::__construct();
		$this->mainColumn = new \PHPFUI\Cell(12, 8, 9);
		$this->addStyleSheet('css/styles.css');
		}

	public function addBody($item) : Page
		{
		$this->mainColumn->add($item);

		return $this;
		}

	public function create(\PHPFUI\Menu $menu) : void
		{
		$this->menu = $menu;

		$link = new \PHPFUI\Link($this->getBaseURL(), $this->getPageName(), false);

		$titleBar = new \PHPFUI\TitleBar($link);
		$hamburger = new \PHPFUI\FAIcon('fas', 'bars', '#');
		$hamburger->addClass('show-for-small-only');
		$titleBar->addLeft($hamburger);
		$titleBar->addLeft('&nbsp;');

		$searchIcon = new \PHPFUI\FAIcon('fas', 'search');
		$this->addSearchModal($searchIcon);
		$titleBar->addRight($searchIcon);
		if (! $this->generating)
			{
			$configIcon = new \PHPFUI\FAIcon('fas', 'cog');
			$this->addConfigModal($configIcon);
			$titleBar->addRight($configIcon);
			}

		$div = new \PHPFUI\HTML5Element('div');
		$stickyTitleBar = new \PHPFUI\Sticky($div);
		$stickyTitleBar->add($titleBar);
		$stickyTitleBar->addAttribute('data-options', 'marginTop:0;');
		$this->add($stickyTitleBar);

		$body = new \PHPFUI\HTML5Element('div');
		$body->addClass('body-info');
		$grid = new \PHPFUI\GridX();
		$menuColumn = new \PHPFUI\Cell(4, 4, 3);
		$menuColumn->addClass('show-for-medium');
		$menuColumn->add($menu);
		$grid->add($menuColumn);

		$this->mainColumn->addClass('main-column');
		$grid->add($this->mainColumn);
		$body->add($grid);

		$offCanvas = new \PHPFUI\OffCanvas($body);
		$div = new \PHPFUI\HTML5Element('div');
		$div->add($menu);
		$offId = $offCanvas->addOff($div, $hamburger);
		$offCanvas->setPosition($offId, 'left')->setTransition($offId, 'over');

		$this->add($offCanvas);

		$footer = new \PHPFUI\TopBar();
		$menu = new \PHPFUI\Menu();
		$menu->addClass('simple');
		$menu->addMenuItem(new \PHPFUI\MenuItem('Powered By'));
		$menu->addMenuItem(new \PHPFUI\MenuItem('PHPFUI/InstaDoc', 'http://www.phpfui.com/?n=PHPFUI\InstaDoc'));
		$menu->addMenuItem(new \PHPFUI\MenuItem('github', 'https://github.com/phpfui/InstaDoc'));

		$footer->addLeft($menu);

		$year = date('Y');
		$footer->addRight("&copy; {$year} Bruce Wells");

		$this->add($footer);
		}

	public function setGenerating(string $generating) : Page
		{
		$this->generating = $generating;

		return $this;
		}

	public function setParameters(array $parameters) : Page
		{
		$this->parameters = $parameters;

		return $this;
		}

	private function addConfigModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this, $modalLink);
		$modal->addClass('small');
		$form = new \PHPFUI\Form($this);
		$form->setAttribute('method', 'get');
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Configuration');
		$parameters = $this->parameters;

		foreach ([Controller::CSS_FILE, Controller::TAB_SIZE, 'submit'] as $value)
			{
			unset($parameters[$value]);
			}

		foreach ($parameters as $name => $value)
			{
			$fieldSet->add(new \PHPFUI\Input\Hidden($name, $value));
			}
		$cssSelector = new CSSSelector($this, $this->parameters[Controller::CSS_FILE]);
		$cssSelector->setLabel('Code Formating Style');
		$cssSelector->setToolTip('Sets the style sheet for PHP code');
		$fieldSet->add($cssSelector);

		$tabStop = new \PHPFUI\Input\Number(Controller::TAB_SIZE, 'Tab Stop Spaces', $this->parameters[Controller::TAB_SIZE]);
		$tabStop->setAttribute('min', 0);
		$tabStop->setAttribute('max', 10);
		$tabStop->setToolTip('Indent tabbed files with this number of spaces');
		$fieldSet->add($tabStop);

		$form->add($fieldSet);
		$submit = new \PHPFUI\Submit('Update');
		$form->add($modal->getButtonAndCancel($submit));
		$modal->add($form);
		}

	private function addSearchModal(\PHPFUI\HTML5Element $modalLink) : void
		{
		$modal = new \PHPFUI\Reveal($this, $modalLink);
		$modal->addClass('small');
		$form = new \PHPFUI\Form($this);
		$form->setAreYouSure(false);
		$fieldSet = new \PHPFUI\FieldSet('Search Namespaces \ Classes');
		$search = new \PHPFUI\Input\SelectAutoComplete($this, 'search');

		$this->addSearchOptions($this->menu, $search);

		$id = $search->getHiddenField()->getId();
		$js = "function goToSearchSelection(){window.location=$('#{$id}').val();}";
		$this->addJavaScript($js);
		$search->addAttribute('onchange', 'goToSearchSelection()');
		$fieldSet->add($search);
		$form->add($fieldSet);
		$modal->add($form);
		}

	private function addSearchOptions(\PHPFUI\Menu $menu, \PHPFUI\Input\Select $search) : void
		{
		foreach ($menu->getMenuItems() as $item)
			{
			if ($item instanceof \PHPFUI\MenuItem)
				{
				$search->addOption(str_replace('\\', '\\\\', $item->getName()), str_replace('\\', '\\\\', $item->getLink()));
				}
			else
				{
				$this->addSearchOptions($item, $search);
				}
			}
		}

	}
