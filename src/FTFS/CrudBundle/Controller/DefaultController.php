<?php

namespace FTFS\CrudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('FTFSCrudBundle:Default:index.html.twig', array('name' => $name));
    }
}
