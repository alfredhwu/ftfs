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

    public function inviteAction()
    {
        $request = $this->getRequest();
        $request->getSession()->set('navbar',$request->getRequestUri());
        $request->getSession()->set('bodymenu',$request->getRequestUri());

        $invitation = new \FTFS\UserBundle\Entity\Invitation;

        $form = $this->createFormBuilder($invitation)
                    ->add('email', 'email')
                    ->add('roles', 'choice', array(
                        'choices' => array(
                            'ROLE_ADMIN' => 'ROLE_ADMIN',
                            'ROLE_AGENT' => 'ROLE_AGENT',
                            'ROLE_CLIENT' => 'ROLE_CLIENT',
                        ),
                        'multiple' => true,
                    ))
                    ->getForm();

        if($request->getMethod() === 'POST')
        { 
            $form->bindRequest($request);

            if($form->isValid())
            {
                $em = $this->getDoctrine()->getEntityManager();
                // test if email already exists
                $em->persist($invitation);
                $em->flush();
                $message = \Swift_Message::newInstance()
                            ->setSubject('Invitation for inscription to Support Service of Fujitsu Telecom France SAS')
                            ->setFrom(array('support@fujitsu-telecom.fr'=>'Support Service - Fujitsu Telecom France SAS'))
                            ->setTo($invitation->getEmail())
                            ->setBody(
                                $this->renderView('FTFSUserBundle:User:invitation_email.html.twig', array(
                                    'code' => $invitation->getCode(), 
                                    'email' => $invitation->getEmail(),
                                )),
                                'text/html'
                            )
                            ->addPart($this->renderView('FTFSUserBundle:User:invitation_email.txt.twig', array(
                                'code' => $invitation->getCode(),
                                'email' => $invitation->getEmail(),
                            )))
                            ;
                $this->get('mailer')->send($message);
                return $this->redirect($this->generateUrl('ftfsuserbundle_user_index'));
            }
        }

        return $this->render('FTFSUserBundle:User:invitation.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}
