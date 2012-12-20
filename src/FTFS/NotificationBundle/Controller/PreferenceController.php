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
        $em = $this->getDoctrine()->getEntityManager();
        $events = $em->getRepository('FTFSNotificationBundle:Event')->findAll();
        $auto_filters = array();
        $other_filters = array();
        foreach($events as $event) {
            $qb_filters = $em->createQueryBuilder()
                            ->select('f')
                            ->from('FTFSNotificationBundle:EventCatchFilterDefault', 'f')
                            ->leftJoin('f.method', 'm')
                            ->where('f.event = :event')
                            ->andWhere('f.auto = :auto')
                            ->orderBy('m.name', 'DESC')
                            ;
            $auto_filters[$event->getEventKey()] = $qb_filters
                            ->setParameter('event', $event->getId())
                            ->setParameter('auto', true)
                            ->getQuery()->getResult();
            $other_filters[$event->getEventKey()] = $qb_filters
                            ->setParameter('event', $event->getId())
                            ->setParameter('auto', false)
                            ->getQuery()->getResult();
        }
        $methods = $em->getRepository('FTFSNotificationBundle:NotificationMethod')->findAll();
        return $this->render('FTFSNotificationBundle:Preference:event_definition.html.twig', array(
            'events' => $events,
            'auto_filters' => $auto_filters,
            'other_filters' => $other_filters,
            'methods' => $methods,
        ));
    }

    /**
     * get a form to modify the event_catch_filter_default options
     */
    public function eventCatchFilterDefaultAction($id, $auto)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $event = $em->getRepository('FTFSNotificationBundle:Event')->find($id);
        if(!$event) {
            throw $this->createNotFoundException('event with id ['.$id.'] not found !');
        }
        $filters = $em->getRepository('FTFSNotificationBundle:EventCatchFilterDefault')->findBy(array(
            'event' => $id,
            'auto' => $auto,
        ));
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
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('m')
                                ->where('m.is_enabled_agent = 1')
                                ->orWhere('m.is_enabled_client = 1');
                        },
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
                    $filter->setAuto($auto);
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
                    'auto' => $auto,
                )),
        ));
    }

    public function eventCatchFilterAction($id, $event_id, $auto)
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
        foreach($filter->getNotificationMethods($user, $event, $auto) as $method) {
            $preferences['methods']->add($method);
            $old_methods[$method->getName()] = $method;   // stock the old methods settings
        }
        if($user->isAgent()) {
            $form = $this->createFormBuilder($preferences)
                        ->add('methods', 'entity', array(
                            'class' => 'FTFSNotificationBundle:NotificationMethod',
                            'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                                return $er->createQueryBuilder('m')
                                    ->where('m.is_enabled_agent = 1');
                            },
                            'multiple' => true,
                            'expanded' => true,
                        ))
                        ->getForm();
        }else{
            $form = $this->createFormBuilder($preferences)
                        ->add('methods', 'entity', array(
                            'class' => 'FTFSNotificationBundle:NotificationMethod',
                            'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                                return $er->createQueryBuilder('m')
                                    ->where('m.is_enabled_client = 1');
                            },
                            'multiple' => true,
                            'expanded' => true,
                        ))
                        ->getForm();
        }
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
                'auto' => $auto,
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
                    $new_user_filter->setAuto($auto);
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
                    $new_user_filter->setAuto($auto);
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
                    'auto' => $auto,
                )),
        ));
    }

    public function eventCatchFilterListAction(\FTFS\UserBundle\Entity\User $user)
    {
        $filter = $this->get('ftfs_notification.filter.event_catch_filter');
        $events = $this->getDoctrine()->getEntityManager()->getRepository('FTFSNotificationBundle:Event')->findAll();
        $auto_methods = array();
        $other_methods = array();

        foreach($events as $event) {
            $auto_methods[$event->getEventKey()] = $filter->getNotificationMethods($user, $event, true);
            $other_methods[$event->getEventKey()] = $filter->getNotificationMethods($user, $event, false);
        }
        return $this->render('FTFSNotificationBundle:Preference:event_catch_filter.html.twig', array(
            'events' => $events,
            'user' => $user,
            'auto_methods' => $auto_methods,
            'other_methods' => $other_methods,
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

    public function eventAddAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $event = new \FTFS\NotificationBundle\Entity\Event;
        $form = $this->createFormBuilder($event)
                    ->add('eventKey')
                    //->add('securityLevel')
                    ->getForm();
        $request = $this->getRequest();
        if('POST'===$request->getMethod()){
            $form->bindRequest($request);
            if($form->isValid()) {
                $event->setSecurityLevel(0);
                $em->persist($event);

                $em->flush();
                return $this->redirect($this->generateUrl('ftfs_notificationbundle_preference_event_definition'));
            }
        }
        return $this->render('FTFSNotificationBundle:Preference:event_definition_add.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function methodDisableAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $method = $em->getRepository('FTFSNotificationBundle:NotificationMethod')
            ->find($id);
        if(!$method) {
            throw $this->createNotFoundException('Method not found');
        }
        $method->setIsEnabled(false);
        $em->flush();
        return $this->redirect($this->generateUrl('ftfs_notificationbundle_preference_event_definition'));
    }

    public function methodEnableAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $method = $em->getRepository('FTFSNotificationBundle:NotificationMethod')
            ->find($id);
        if(!$method) {
            throw $this->createNotFoundException('Method not found');
        }
        $method->setIsEnabled(true);
        $em->flush();
        return $this->redirect($this->generateUrl('ftfs_notificationbundle_preference_event_definition'));
    }
}
