<?php

namespace FGS\GestionComptesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;



class MailController extends Controller
{
	public function testMailAction()
	{
		$mail = new \Swift_Message;
		$mail->setSubject('It\'s Working')
			->setFrom('do-not-reply@ma-petite-tirelire.fr')
			->setTo('web-s8hft@mail-tester.com')
			->setContentType('text/html')
			->setBody('<h1>It\'s Working  ! ! !</h1><p>On peut envoyer des mails de test !  ! !</p>');
		
		$this->get('mailer')->send($mail);

		return new Response('<html><head><title>test</title></head><body>test</body></html>');
	}
}
