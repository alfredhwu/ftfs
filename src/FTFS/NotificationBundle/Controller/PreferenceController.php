<?php

namespace FTFS\NotificationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class PreferenceController extends Controller
{
    public function indexAction()
    {
        return $this->render('FTFSNotificationBundle:Preference:index.html.twig');
    }

    public function eventDefinitionAction()
    {
        $events = $this->getDoctrine()->getEntityManager()->getRepository('FTFSNotificationBundle:Event')->findAll();
        //throw new \Exception('coucou');
        return $this->render('FTFSNotificationBundle:Preference:event_definition.html.twig', array(
            'events' => $events,
        ));
    }

    public function eventAddAction()
    {
//        throw new \Exception('coucou');
        $em = $this->getDoctrine()->getEntityManager();
        //$event = new \FTFS\NotificationBundle\Entity\Event;
        $event = array('event' => 'new event definition');
        $form = $this->createFormBuilder($event)
                    ->add('eventKey')
                    ->add('securityLevel')
                    ->add('methods', 'entity', array(
                        'class' => 'FTFSNotificationBundle:NotificationMethod',
                        'multiple' => true,
                        'expanded' => true,
                    ))
                    ->getForm();
        $request = $this->getRequest();
        if('POST'===$request->getMethod()){
            $form->bindRequest($request);
            if($form->isValid()) {
                // flush
                //throw new \Exception('coucou');
                $new_event = new \FTFS\NotificationBundle\Entity\Event;
                $data = $form->getData();
                $new_event->setEventKey($data['eventKey']);
                $new_event->setSecurityLevel($data['securityLevel']);
                $em->persist($new_event);

                foreach($data['methods'] as $method) {
                    $filter = new \FTFS\NotificationBundle\Entity\EventCatchFilterDefault;
                    $filter->setEvent($new_event);
                    $filter->setMethod($method);
                    $em->persist($filter);
                }

                $em->flush();

                return $this->redirect($this->generateUrl('ftfs_notificationbundle_preference_event_definition'));
            }
        }
        return $this->render('FTFSNotificationBundle:Preference:event_definition_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function eventCatchFilterDefaultAction(\FTFS\NotificationBundle\Entity\Event $event)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $filters = $em->getRepository('FTFSNotificationBundle:EventCatchFilterDefault')->findByEvent($event->getId());
        $preferences = array('preferences' => 'default notification preferences');
        $old_methods = array();
        $preferences['methods'] = new \Doctrine\Common\Collections\ArrayCollection;
        foreach($filters as $filter) {
            $preferences['methods']->add($filter->getMethod());
            $old_methods[$filter->getMethod()->getName()] = $filter;   // stock the old methods settings
        }
        $form = $this->createFormBuilder($preferences)
                    ->add('methods', 'entity', array(
                        'class' => 'FTFSNotificationBundle:NotificationMethod',
                        'multiple' => true,
                        'expanded' => true,
                    ))
                    ->getForm();
        $request = $this->getRequest();
        if('POST'===$request->getMethod()){
            $form->bindRequest($request);

            $data = $form->getData();
            $new_methods = array();
            foreach($data['methods'] as $new_method){
                $new_methods[$new_method->getName()] = $new_method;
            };
            // delete 
            foreach($old_methods as $old_method => $filter) {
                if(!array_key_exists($old_method, $new_methods)) {
                    $em->remove($filter);
                } 
            }
            // add
            foreach($new_methods as $new_method => $method) {
                if(!array_key_exists($new_method, $old_methods)) {
                    $filter = new \FTFS\NotificationBundle\Entity\EventCatchFilterDefault;
                    $filter->setEvent($event);
                    $filter->setMethod($method);
                    $em->persist($filter);
                }
            }
            $em->flush();
            return $this->redirect(
                $this->generateUrl('ftfs_notificationbundle_preference_event_definition', array(
                    'id' => $event->getId(),
            )));
        }
        return $this->render('FTFSNotificationBundle:Preference:event_catch_filter_form.html.twig', array(
            'form' => $form->createView(),
            'action' => $this->generateUrl('ftfs_notificationbundle_preference_event_catch_filter_default', array(
                    'id' => $event->getId(),
                )),
        ));
    }

    public function eventCatchFilterListAction(\FTFS\UserBundle\Entity\User $user)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $events = $em->getRepository('FTFSNotificationBundle:Event')->findAll();
        return $this->render('FTFSNotificationBundle:Preference:event_catch_filter.html.twig', array(
            'user' => $user,
            'events' => $events,
        ));
    }

    public function eventCatchFilterResetAction(\FTFS\UserBundle\Entity\User $user)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user_filters = $em->getRepository('FTFSNotificationBundle:EventCatchFilter')->findAll();
        foreach($user_filters as $filter) {
            $em->remove($filter);
        }
        $em->flush();
        return $this->redirect(
            $this->generateUrl('ftfs_notificationbundle_preference_event_catch_filter_index', array(
            'id' => $user->getId(),
        )));
    }

    public function eventCatchFilterAction($id, $event_id)
    {   
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('\FTFS\UserBundle\Entity\User')->find($id);
        $event = $em->getRepository('\FTFS\NotificationBundle\Entity\Event')->find($event_id);
        if(!$user) {
            throw $this->createNotFoundException('User not found ...');
        }
        if(!$event) {
            throw $this->createNotFoundException('Event does not exist ...');
        }

        $filter = $this->get('ftfs_notification.filter.event_catch_filter');
        $old_methods = array();
        $preferences['methods'] = new \Doctrine\Common\Collections\ArrayCollection;
        foreach($filter->getNotificationMethods($user, $event) as $method) {
            $preferences['methods']->add($method);
            $old_methods[$method->getName()] = $method;   // stock the old methods settings
        }
        $form = $this->createFormBuilder($preferences)
                    ->add('methods', 'entity', array(
                        'class' => 'FTFSNotificationBundle:NotificationMethod',
                        'multiple' => true,
                        'expanded' => true,
                    ))
                    ->getForm();
        $request = $this->getRequest();
        if('POST'===$request->getMethod()){
            $form->bindRequest($request);
            $data = $form->getData();
            $new_methods = array();
            foreach($data['methods'] as $new_method){
                $new_methods[$new_method->getName()] = $new_method;
            };

            $user_filters = array();
            $filters = $em->getRepository('FTFSNotificationBundle:EventCatchFilter')->findBy(array(
                'user' => $user->getId(),
                'event' => $event->getId(),
            ));
            foreach($filters as $filter) {
                $user_filters[$filter->getMethod()->getName()] = $filter;
            }

            // delete 
            foreach(array_diff_key($old_methods, $new_methods) as $method_name => $method) {
                if(array_key_exists($method_name, $user_filters)) {
                    $user_filters[$method_name]->setAllow(false);
                }else{
                    $new_user_filter = new \FTFS\NotificationBundle\Entity\EventCatchFilter;
                    $new_user_filter->setEvent($event);
                    $new_user_filter->setUser($user);
                    $new_user_filter->setMethod($method);
                    $new_user_filter->setAllow(false);
                    $em->persist($new_user_filter);
                }
            }
            // add
            foreach(array_diff_key($new_methods, $old_methods) as $method_name => $method) {
                if(array_key_exists($method_name, $user_filters)) {
                    $user_filters[$method_name]->setAllow(true);
                }else{
                    $new_user_filter = new \FTFS\NotificationBundle\Entity\EventCatchFilter;
                    $new_user_filter->setEvent($event);
                    $new_user_filter->setUser($user);
                    $new_user_filter->setMethod($method);
                    $new_user_filter->setAllow(true);
                    $em->persist($new_user_filter);
                }
            }
            $em->flush();
            return $this->redirect(
                $this->generateUrl('ftfs_notificationbundle_preference_event_catch_filter_index', array(
                    'id' => $user->getId(),
            )));
        }
        return $this->render('FTFSNotificationBundle:Preference:event_catch_filter_form.html.twig', array(
            'form' => $form->createView(),
            'action' => $this->generateUrl('ftfs_notificationbundle_preference_event_catch_filter', array(
                    'id' => $user->getId(),
                    'event_id' => $event->getId(),
                )),
        ));
    }
}
