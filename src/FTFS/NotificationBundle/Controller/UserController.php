<?php

namespace FTFS\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class UserController extends Controller
{
    public function indexAction()
    {
        $notifications = $this->getDoctrine()->getEntityManager()
            ->getRepository('\FTFS\NotificationBundle\Entity\NotificationLog')->findBy(array(
                'method' => 1,
                'notified_to' => $this->get('security.context')->getToken()->getUser()->getId(),
            ), array(
                'id' => 'desc',
            ));
        return $this->render('FTFSNotificationBundle:User:index.html.twig', array(
            'notifications' => $notifications,
        ));
    }

    public function systemNotificationRemoveAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $notification = $em->getRepository('\FTFS\NotificationBundle\Entity\NotificationLog')->find($id);
        if(!$notification) {
            throw $this->createNotFoundException('notification not found');
        }
        $em->remove($notification);
        $em->flush();
        return new \Symfony\Componenet\HttpFoundation\Response('notification :'.$id.' removed successfully !');
    }

    public function systemNotificationClearAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $notifications = $em->getRepository('\FTFS\NotificationBundle\Entity\NotificationLog')->findBy(array(
                'method' => 1,
                'notified_to' => $this->get('security.context')->getToken()->getUser()->getId(),
            ));
        $notifications_new = $em->getRepository('\FTFS\NotificationBundle\Entity\NotificationLog')->findBy(array(
                'method' => 1,
                'notified_at' => null,
                'notified_to' => $this->get('security.context')->getToken()->getUser()->getId(),
            ));
        foreach($notifications as $notification) {
            if(!in_array($notification, $notifications_new)){
                $em->remove($notification);
            }
        }
        $em->flush();
        return $this->redirect($this->generateUrl('ftfs_notificationbundle_user_index'));
    }

    /**
     * ajax connexion for counting list
     */
    public function getOneNotificationAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $notification = $em->getRepository('\FTFS\NotificationBundle\Entity\NotificationLog')
            ->findOneBy(array(
                'method' => 1,
                'notified_at' => null,
                'notified_to' => $this->get('security.context')->getToken()->getUser()->getId(),
            ));
        if(!$notification) {
            return new \Symfony\Component\HttpFoundation\Response();
        }
        //throw new \Exception(count($notification));
        $notification->setNotifiedAt(new \DateTime('now'));
        $em->flush();
        return new \Symfony\Component\HttpFoundation\Response($notification->getMiniMessage());
    }

    public function getNotificationCountAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $notifications = $em->getRepository('\FTFS\NotificationBundle\Entity\NotificationLog')
            ->findBy(array(
                'method' => 1,
                //'notified_at' => null,
                'notified_to' => $this->get('security.context')->getToken()->getUser()->getId(),
            ));
        $session = $this->getRequest()->getSession();
        $name = 'counter-notification';
        $count = count($notifications);
        if($session->has($name)) {
            $session->remove($name);
        }
        $session->set($name, $count);
        return new \Symfony\Component\HttpFoundation\Response($count);
    }
}
