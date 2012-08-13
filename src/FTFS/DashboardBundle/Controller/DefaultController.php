<?php

namespace FTFS\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Do not replay !!!')
            ->setFrom('noreply@fujitsu-telecom.fr')
            ->setTo('alfredhwu@gmail.com')
            ->setBody('this is an automatic mail send by ftfs support service !!!')
        ;
        $this->get('mailer')->send($message);
        return $this->render('FTFSDashboardBundle:Default:index.html.twig', array('name' => $name));
    }
}
