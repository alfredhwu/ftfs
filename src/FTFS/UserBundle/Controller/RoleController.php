<?php

namespace FTFS\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FTFS\UserBundle\Entity\User;
use FTFS\UserBundle\Form\Type\EditFormType;

class RoleController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('FTFSUserBundle:Role:index.html.twig');
    }
}
