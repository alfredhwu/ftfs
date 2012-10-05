<?php

namespace FTFS\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('FTFSProductBundle:Default:index.html.twig', array('name' => 'coucou'));
    }
}
