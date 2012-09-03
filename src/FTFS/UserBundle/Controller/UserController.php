<?php

namespace FTFS\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FTFS\UserBundle\Entity\User;
use FTFS\UserBundle\Form\Type\EditFormType;
use FTFS\UserBundle\Form\Type\EditRolesFormType;

class UserController extends Controller
{
    
    public function indexAction()
    {
        $request = $this->getRequest();
        $request->getSession()->set('navbar',$request->getRequestUri());
        $request->getSession()->set('bodymenu',$request->getRequestUri());

        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return $this->render('FTFSUserBundle:User:index.html.twig', array('users' => $users));
    }

    public function showAction($username)
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return $this->render('FTFSUserBundle:User:index.html.twig', array('users' => $users));
    }

    public function editAction($username)
    {
        $request = $this->getRequest();
        $request->getSession()->set('navbar',$request->getRequestUri());
        $request->getSession()->set('bodymenu',$request->getRequestUri());

        $user = $this->get('fos_user.user_manager')->findUserByUsername($username);
        $form = $this->createForm(new EditFormType('\FTFS\UserBundle\Entity\User'), $user);

        return $this->render('FTFSUserBundle:User:edit.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction($username)
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return $this->render('FTFSUserBundle:User:index.html.twig', array('users' => $users));
    }

    public function editRolesAction($username)
    {
        $request = $this->getRequest();
        $request->getSession()->set('navbar',$request->getRequestUri());
        $request->getSession()->set('bodymenu',$request->getRequestUri());

        $user = $this->get('fos_user.user_manager')->findUserByUsername($username);
        $form = $this->createForm(new EditRolesFormType('\FTFS\UserBundle\Entity\User'), $user);
        $request = $this->get('request');

        if($request->getMethod() === 'POST')
        { 
            $form->bindRequest($request);

            if($form->isValid())
            {
                $this->get('fos_user.user_manager')->updateUser($user);
            }
            return $this->redirect($this->generateUrl('ftfsuserbundle_user_index'));
        }

        return $this->render('FTFSUserBundle:User:edit_roles.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    public function editPasswordAction(User $user)
    {
        return null;
    }

}
