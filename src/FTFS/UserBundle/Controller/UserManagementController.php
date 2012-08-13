<?php

namespace FTFS\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class UserManagementController extends Controller
{
    
    public function indexAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        
        // prob with pattern check in swiftmailer in vendor/swiftmailer/lib/classes/Swift/Mime/Headers/MailboxHeader.php line 238
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('d.wu@fujitsu-telecom.fr')
            ->setTo('alfredhwu@gmail.com')
            ->setBody('hello world seconde bizarre');
        $this->get('mailer')->send($message);
        return $this->render('FTFSUserBundle:UserManagement:index.html.twig', array('users' => $users));
    }
}
