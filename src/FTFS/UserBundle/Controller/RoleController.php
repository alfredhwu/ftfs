<?php

namespace FTFS\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FTFS\UserBundle\Entity\User;
use FTFS\UserBundle\Form\Type\EditFormType;

class RoleController extends Controller
{
    
    public function indexAction()
    {
        $request = $this->getRequest();
        $request->getSession()->set('navbar',$request->getRequestUri());
        $request->getSession()->set('bodymenu',$request->getRequestUri());

        return $this->render('FTFSUserBundle:Role:index.html.twig');
    }
}
