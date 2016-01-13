<?php

namespace FGS\GestionComptesBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AideController extends Controller
{
    public function bienvenueAction()
    {
		//génération de la vue
    	return $this->render('FGSGestionComptesBundle:Presentation:welcome.html.twig', array(
    		
     	));

    }
}