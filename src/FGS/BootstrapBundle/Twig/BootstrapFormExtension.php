<?php 
namespace FGS\BootstrapBundle\Twig;

class BootstrapFormExtension extends \Twig_Extension
{
	private $presentation 	= "inline";
	
	private $labelGrid	= array();
	private $widgetGrid	= array();
	private $errorGrid	= array();
	private $buttonGrid = array();
	
	private $submitGrid	= array();
	private $resetGrid 	= array();
	
	
	private $allowedGridInfos	= array("xs","sm","md","lg", "xs-offset", "sm-offset", "md-offset", "lg-offset", "xs-push", "sm-push", "md-push", "lg-push");
	private $allowedMinColumn	= 1;
	private $allowedMaxColumn	= 12;
	

	public function getFunctions() {
		return array(
				//méthode permettant d'indiquer la présentation du formulaire
				new \Twig_SimpleFunction('set_bootstrap_form_presentation', array($this, 'setBootratpFormPresentation')),
				new \Twig_SimpleFunction('get_bootstrap_form_presentation', array($this, 'getBootratpFormPresentation')),
				
				//méthode transformant un array contenant des infos bootstrap sur la taille l'offset ou le push d'un élément
				new \Twig_SimpleFunction('bootstrap_grid', array($this, 'bootstrapGrid')),
				
				//méthode permettant d'indiquer les informations bootstrap sur le label
				new \Twig_SimpleFunction('set_bootstrap_label_grid', 	array($this, 'setBootratpLabelGrid')),
				new \Twig_SimpleFunction('get_bootstrap_label_grid', 	array($this, 'getBootratpLabelGrid')),
				
				//méthode permettant d'indiquer les informations bootstrap sur le widget
				new \Twig_SimpleFunction('set_bootstrap_widget_grid', 	array($this, 'setBootratpWidgetGrid')),
				new \Twig_SimpleFunction('get_bootstrap_widget_grid', 	array($this, 'getBootratpWidgetGrid')),
				
				//méthode permettant d'indiquer les informations bootstrap sur l'erreur
				new \Twig_SimpleFunction('set_bootstrap_error_grid', 	array($this, 'setBootratpErrorGrid')),
				new \Twig_SimpleFunction('get_bootstrap_error_grid',	array($this, 'getBootratpErrorGrid')),
				
				//méthode permettant d'indiquer les informations bootstrap sur les boutons
				new \Twig_SimpleFunction('set_bootstrap_button_grid', 	array($this, 'setBootratpButtonGrid')),
				new \Twig_SimpleFunction('get_bootstrap_button_grid',	array($this, 'getBootratpButtonGrid')),

				//méthode permettant d'indiquer les informations bootstrap sur les boutons submit
				new \Twig_SimpleFunction('set_bootstrap_submit_grid', 	array($this, 'setBootratpSubmitGrid')),
				new \Twig_SimpleFunction('get_bootstrap_submit_grid',	array($this, 'getBootratpSubmitGrid')),
				
				//méthode permettant d'indiquer les informations bootstrap sur les boutons submit
				new \Twig_SimpleFunction('set_bootstrap_reset_grid', 	array($this, 'setBootratpResetGrid')),
				new \Twig_SimpleFunction('get_bootstrap_reset_grid',	array($this, 'getBootratpResetGrid')),
				
				
		);
	}

	/**
	 * Configuration de présentation du formulaire
	 * 
	 */
	public function setBootratpFormPresentation($presentation) 
	{
		$this->presentation	=	$presentation;
	}
	/**
	 * Récupération de la présentation du formulaire
	 * @return string
	 */
	public function getBootratpFormPresentation() 
	{
		return $this->presentation;
	}
	

	/**
	 * Configuration de la taille "bootstrap" du label
	 * @param array $grid
	 */
	public function setBootratpLabelGrid($grid) 
	{
		$this->labelGrid	= 	$this->filterGrid($grid);
	}
	/**
	 * Récupération de la taille "bootstrap" du label
	 * @return array:
	 */
	public function getBootratpLabelGrid() 
	{
		return $this->labelGrid;
	}
	
	/**
	 * Configuration de la taille "bootstrap" du widget
	 * @param array $grid
	 */
	public function setBootratpWidgetGrid($grid) 
	{
		$this->widgetGrid	= 	$this->filterGrid($grid);
	}
	/**
	 * Récupération de la taille "bootstrap" du label
	 * @return array:
	 */
	public function getBootratpWidgetGrid() 
	{
		return $this->widgetGrid;
	}
	
	/**
	 * Configuration de la taille "bootstrap" de l'erreur (quand il y en aura)
	 * @param array $grid
	 */
	public function setBootratpErrorGrid($grid)
	{
		$this->errorGrid	= 	$this->filterGrid($grid);
	}
	/**
	 * Récupération de la taille "bootstrap" de l'erreur (quand il y en aura)
	 * @return array:
	 */
	public function getBootratpErrorGrid()
	{
		return $this->errorGrid;
	}

	
	
	/**
	 * Configuration de la taille "bootstrap" des boutons
	 * @param array $grid
	 */
	public function setBootratpButtonGrid($grid)
	{
		$this->buttonGrid	= 	$this->filterGrid($grid);
	}
	/**
	 * Récupération de la taille "bootstrap" des boutons
	 * @return array:
	 */
	public function getBootratpButtonGrid()
	{
		return $this->buttonGrid;
	}
	
	

	
	/**
	 * Configuration de la taille "bootstrap" des boutons de type "submit"
	 * @param array $grid
	 */
	public function setBootratpSubmitGrid($grid)
	{
		$this->submitGrid	= 	$this->filterGrid($grid);
	}
	/**
	 * Récupération de la taille "bootstrap" des boutons de type "submit"
	 * @return array:
	 */
	public function getBootratpSubmitGrid()
	{
		return $this->submitGrid;
	}
	
	
	
	/**
	 * Configuration de la taille "bootstrap" des boutons de type "submit"
	 * @param array $grid
	 */
	public function setBootratpResetGrid($grid)
	{
		$this->resetGrid	= 	$this->filterGrid($grid);
	}
	/**
	 * Récupération de la taille "bootstrap" des boutons de type "submit"
	 * @return array:
	 */
	public function getBootratpResetGrid()
	{
		return $this->resetGrid;
	}
	
	
	
	
	
	public function bootstrapGrid($grid)
	{
		if (!is_array($grid))
			return;
			
		$tempo	=	'';
	
		foreach ($this -> filterGrid($grid) as $key => $value)
		{
			(empty($tempo)) ?  : $tempo .= ' ';
			$tempo .= "col-$key-$value";
		}
		return $tempo;
	}

	
	private function filterGrid($array_grid)
	{
		return array_filter($array_grid, function ($value, $key) {
			return in_array($key, $this->allowedGridInfos) && is_int($value) && ($value >=1) && ($value<=12);
		}, ARRAY_FILTER_USE_BOTH);
	}
	
	
	public function getName()
	{
		return 'bootstrap_form_extension';
	}

}