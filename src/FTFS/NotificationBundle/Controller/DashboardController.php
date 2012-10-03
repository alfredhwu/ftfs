<?php

namespace FTFS\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DashboardController extends Controller
{
    public function indexAction()
    {
        return $this->render('FTFSNotificationBundle:Dashboard:index.html.twig');
    }
}
