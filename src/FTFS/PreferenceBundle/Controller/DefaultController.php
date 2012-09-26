<?php

namespace FTFS\PreferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('FTFSPreferenceBundle:Default:index.html.twig');
    }
}
