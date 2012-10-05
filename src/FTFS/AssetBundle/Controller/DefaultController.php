<?php

namespace FTFS\AssetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('FTFSAssetBundle:Default:index.html.twig', array('name' => 'coucou'));
    }
}
