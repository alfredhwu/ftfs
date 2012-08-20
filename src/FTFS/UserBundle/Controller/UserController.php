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
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return $this->render('FTFSUserBundle:User:index.html.twig', array('users' => $users));
    }

    public function showAction($id)
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return $this->render('FTFSUserBundle:User:index.html.twig', array('users' => $users));
    }

    public function editAction(User $user)
    {
        $form = $this->createForm(new EditFormType('\FTFS\UserBundle\Entity\User'), $user);

        return $this->render('FTFSUserBundle:User:edit.html.twig', array(
            'id' => $user->getId(),
            'entity' => $user,
            'form' => $form->createView(),
        ));
    }

    public function deleteAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
        return $this->render('FTFSUserBundle:User:index.html.twig', array('users' => $users));
    }

    public function editRolesAction(User $user)
    {
        //$form = $this->createForm(new EditRolesFormType('\FTFS\UserBundle\Entity\User'), $user);
        $defaultData = array('message' => 'msg');
        $form = $this->createFormBuilder($defaultData)
            ->add('role', 'choice', array(
                'choices' => array(
                    'ROLE_ADMIN' => 'administrator',
                    'ROLE_AGENT' => 'agent',
                    'ROLE_CLIENT' => 'client',
                ),
            ))->getForm(); 

        $request = $this->get('request');

        if($request->getMethod() === 'POST')
        { 
            $form->bindRequest($request);

            if($form->isValid())
            {
                $roles = $user->getRoles();
                foreach($roles as $role)
                {
                    $user->removeRole($role);
                }
                $this->get('fos_user.user_manager')->updateUser($user);
        //        $data = $form->getData();
         //       $user->addRole($data['role']);
            }
            return $this->render('FTFSUserBundle:User:edit_roles.html.twig', array(
                'id' => $user->getId(),
                'user' => $user,
                'form' => $form->createView(),
            ));
        }

        return $this->render('FTFSUserBundle:User:edit_roles.html.twig', array(
            'id' => $user->getId(),
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    public function editPasswordAction(User $user)
    {
        return null;
    }

}
