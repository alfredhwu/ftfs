<?php

namespace FTFS\ServiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        //throw new \Exception($request->getSession()->get('navbar'));
        return $this->render('FTFSServiceBundle:Default:index.html.twig');
    }
}
