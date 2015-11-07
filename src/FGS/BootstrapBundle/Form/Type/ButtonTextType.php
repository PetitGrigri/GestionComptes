<?php 
namespace FGS\BootstrapBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class ButtonTextType extends AbstractType
{
    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'button_text';
    }
}
