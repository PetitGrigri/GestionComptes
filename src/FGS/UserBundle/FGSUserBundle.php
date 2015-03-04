<?php

namespace FGS\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FGSUserBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}

}
