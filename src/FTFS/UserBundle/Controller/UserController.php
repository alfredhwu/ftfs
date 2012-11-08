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
        /*
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();
         */
        $role = $this->getRequest()->get('role');
        $is_agent = $role === 'agent' ? true : false;
        $users = $this->getDoctrine()->getEntityManager()
            ->getRepository('FTFSUserBundle:User')->findBy(array(
                'is_agent' => $is_agent,
            ));
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
        $user = $this->get('fos_user.user_manager')->findUserByUsername($username);
        $form = $this->createForm(new EditRolesFormType('\FTFS\UserBundle\Entity\User'), $user);
        $request = $this->get('request');

        if($request->getMethod() === 'POST')
        { 
            $form->bindRequest($request);

            if($form->isValid())
            {
                $this->get('fos_user.user_manager')->updateUser($user);
                // add an indicator
                $user->setIsAgent($user->isAgent());
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

    public function lockAction(User $user)
    {
        $user->setLocked(true);
        $this->getDoctrine()->getEntityManager()->flush();
        return $this->redirect($this->generateUrl('ftfsuserbundle_user_index'));
    }

    public function unlockAction(User $user)
    {
        $user->setLocked(false);
        $this->getDoctrine()->getEntityManager()->flush();
        return $this->redirect($this->generateUrl('ftfsuserbundle_user_index'));
    }

    public function inviteAction()
    {
        $request = $this->getRequest();

        $invitation = new \FTFS\UserBundle\Entity\Invitation;

        $form = $this->createFormBuilder($invitation)
                    ->add('email', 'email')
                    ->add('company')
                    ->add('roles', 'choice', array(
                        'choices' => array(
                            'ROLE_CLIENT' => 'ROLE_CLIENT',
                            'ROLE_CLIENT_COMPANY' => 'ROLE_CLIENT_COMPANY',
                            'ROLE_ADMIN' => 'ROLE_ADMIN',
                            'ROLE_AGENT' => 'ROLE_AGENT',
                            'ROLE_COORDINATOR' => 'ROLE_COORDINATOR',
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

                return $this->redirect($this->generateUrl('ftfsuserbundle_user_invitation_send', array('code'=>$invitation->getCode())));
            }
        }

        return $this->render('FTFSUserBundle:User:invitation.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function invitationSendAction($code)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $invitation = $em->getRepository('FTFSUserBundle:Invitation')->findOneByCode($code);
        if(!$invitation) {
            throw $this->createNotFoundException('invitaion not found !');
        }
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
        $result = $this->get('mailer')->send($message);
        if($result > 0) {
            // callback: if sent, updating invitation
            $invitation->send();
            $em->flush();
        }
        return $this->redirect($this->generateUrl('ftfsuserbundle_user_invitation_list'));
    }

    public function invitationListAction()
    {
        $invitations = $this->getDoctrine()->getEntityManager()
            ->getRepository('FTFSUserBundle:Invitation')->findBy(
                array('accepted' => false),
                array('email' => 'asc')
            );
        return $this->render('FTFSUserBundle:User:invitation_list.html.twig', array(
            'invitations' => $invitations,
        ));
    }

    public function invitationDeleteAction($code)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $invitation = $em->getRepository('FTFSUserBundle:Invitation')->findOneByCode($code);
        if(!$invitation) {
            throw $this->createNotFoundException('invitaion not found !');
        }
        $em->remove($invitation);
        $em->flush();

        return $this->redirect($this->generateUrl('ftfsuserbundle_user_index'));
    }
}
