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

    public function eventCatchFilterAction()
    {
        return $this->render('FTFSNotificationBundle:Preference:event_catch_filter_default.html.twig');
    }
}
