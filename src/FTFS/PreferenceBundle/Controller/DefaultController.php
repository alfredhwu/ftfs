<?php

namespace FTFS\PreferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('FTFSPreferenceBundle:Default:index.html.twig');
    }

    public function userPreferenceAction(\FTFS\UserBundle\Entity\User $user)
    {
        return $this->render('FTFSPreferenceBundle:Default:user_preference.html.twig', array(
            'user' => $user,
        ));
    }

}
