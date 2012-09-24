<?php

namespace FTFS\MailerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('FTFSMailerBundle:Default:index.html.twig', array('name' => $name));
    }
}
