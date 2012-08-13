<?php

namespace FTFS\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('support@fujitsu-telecom.fr')
            ->setTo('fujitsukefu@gmail.com')
            ->setBody('hello coucou !!!')
        ;
        $this->get('mailer')->send($message);
        return $this->render('FTFSDashboardBundle:Default:index.html.twig', array('name' => $name));
    }
}
