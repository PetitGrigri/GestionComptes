<?php 
namespace FGS\BootstrapBundle\Twig;

class BootstrapFormExtension extends \Twig_Extension
{
	public function getFunctions() {
		return array(

				//méthode permettant d'indiquer les informations bootstrap sur le label
				new \Twig_SimpleFunction('get_start_white_space', 	array($this, 'getStartWhiteSpace')),

		);
	}

	/**
	 * Configuration de la taille "bootstrap" du label
	 * @param array $grid
	 */
	public function getStartWhiteSpace($string) 
	{
		$array_char = str_split($string);
		$compteur	= 0;
		
		while ($array_char[$compteur] == ' ') {
			$compteur++;
		}
		return $compteur;
	}

	
	public function getName()
	{
		return 'bootstrap_form_extension';
	}
}
