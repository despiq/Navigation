<?php

/**
 * Navigation
 *
 * @author Jan Marek
 * @license MIT
 */
class Navigation extends \Nette\Application\Control {

	/** @var NavigationNode */
	private $homepage;

	/** @var NavigationNode */
	private $current;

	/** @var bool */
	private $useHomepage = false;
	
	/** @var \Nette\ITranslator */
	private $translator;


	/**
	 * Set node as current
	 * @param NavigationNode $node
	 */
	public function setCurrent(NavigationNode $node) {
		if (isset($this->current)) {
			$this->current->isCurrent = false;
		}
		$node->isCurrent = true;
		$this->current = $node;
	}
	
	/**
	 * Set translator
	 * @param \Nette\ITranslator $translator
	 */
	public function setTranslator(\Nette\ITranslator $translator) {
		$this->translator = $translator;
	}


	/**
	 * Add navigation node as a child
	 * @param string $label
	 * @param string $url
	 * @return NavigationNode
	 */
	public function add($label, $url) {
		return $this->getComponent("homepage")->add($label, $url);
	}


	/**
	 * Setup homepage
	 * @param string $label
	 * @param string $url
	 * @return Navigation
	 */
	public function setupHomepage($label, $url) {
		$homepage = $this->getComponent("homepage");
		$homepage->label = $label;
		$homepage->url = $url;
		$this->useHomepage = true;
		return $homepage;
	}


	/**
	 * Homepage factory
	 * @param string $name
	 */
	protected function createComponentHomepage($name) {
		new NavigationNode($this, $name);
	}


	/**
	 * Render menu
	 * @param bool $renderChildren
	 * @param NavigationNode $base
	 * @param bool $renderHomepage
	 */
	public function renderMenu($renderChildren = true, $base = null, $renderHomepage = true) {
		$template = $this->createTemplate()
			->setFile(dirname(__FILE__) . "/menu.phtml");
		$template->homepage = $base ? $base : $this->getComponent("homepage");
		$template->useHomepage = $this->useHomepage && $renderHomepage;
		$template->renderChildren = $renderChildren;
		$template->children = $this->getComponent("homepage")->getComponents();
		if(isset($this->translator)) {
			$template->setTranslator($this->translator);
			$template->setFile(dirname(__FILE__) . "/menu_translator.phtml");
		}
		$template->render();
	}


	/**
	 * Render full menu
	 */
	public function render() {
		$this->renderMenu();
	}


	/**
	 * Render main menu
	 */
	public function renderMainMenu() {
		$this->renderMenu(false);
	}

	
	/**
	 * Render breadcrumbs
	 */
	public function renderBreadcrumbs() {
		if (empty($this->current)) return;

		$items = array();
		$node = $this->current;

		while ($node instanceof NavigationNode) {
			$parent = $node->getParent();
			if (!$this->useHomepage && !($parent instanceof NavigationNode)) break;

			array_unshift($items, $node);
			$node = $parent;
		}

		$template = $this->createTemplate()
			->setFile(dirname(__FILE__) . "/breadcrumbs.phtml");
		$template->items = $items;
		$template->render();
	}

}