<?php

namespace FTFS\PreferenceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        // default options 
        $default_options = array();
        $default_locale = $this->get('ftfs_configurator')->get('locale');
        if($default_locale) { 
            $default_options['locale'] = $default_locale;
        }else{
            $default_options['locale'] = 'en_US';
        }
        $default_timezone = $this->get('ftfs_configurator')->get('timezone');
        if($default_timezone) { 
            $default_options['timezone'] = $default_timezone;
        }else{
            $default_options['timezone'] = 'Europe/Paris';
        }

        // form
        $form = $this->createFormBuilder(array('list' => 'system configurations'))
            ->add('timezone', 'timezone', array(
                //'preferred_choices'=>array($default_options['timezone']),
            ))
            ->getForm();


        return $this->render('FTFSPreferenceBundle:Default:index.html.twig', array(
            'options' => $default_options,
            'form' => $form->createView(),
        ));
    }

    public function setLocaleAction($locale)
    {
        $this->get('ftfs_configurator')->set('locale', $locale);

        $this->get('session')->setLocale($locale);
        return $this->redirect($this->generateUrl('ftfs_preferencebundle_homepage', array('_locale'=>$locale)));
    }

    public function setTimeZoneAction()
    {
        $timezone = $this->getRequest()->get('timezone');
        //throw new \Exception($timezone);
        if($timezone) {
            $this->get('ftfs_configurator')->set('timezone', $timezone);
            //$this->get('session')->setTimezone($timezone);
        }

        return $this->redirect($this->generateUrl('ftfs_preferencebundle_homepage'));
    }


    public function userPreferenceAction(\FTFS\UserBundle\Entity\User $user)
    {
        // options 
        $options = array();
        // return user locale if setted, otherwise return system locale
        $locale = $this->get('ftfs_configurator')->get('locale', $user);
        if($locale) { 
            $options['locale'] = $locale;
        }else{
            $options['locale'] = 'en_US';
        }
        // the same
        $timezone = $this->get('ftfs_configurator')->get('timezone', $user);
        if($timezone) { 
            $options['timezone'] = $timezone;
        }else{
            $options['timezone'] = 'Europe/Paris';
        }

        // form
        $form = $this->createFormBuilder(array('list' => 'user configurations'))
            ->add('timezone', 'timezone', array(
                //'preferred_choices'=>array($options['timezone']),
            ))
            ->getForm();


        return $this->render('FTFSPreferenceBundle:Default:user_preference.html.twig', array(
            'user' => $user,
            'options' => $options,
            'form' => $form->createView(),
        ));
    }

    public function setUserLocaleAction($locale, $id)
    {
        $user = $this->getDoctrine()->getEntityManager()->getRepository('FTFSUserBundle:User')->find($id);
        if(!$user) {
            throw $this->createNotFoundException('user not found !');
        }
        $this->get('ftfs_configurator')->set('locale', $locale, $user);

        $this->get('session')->setLocale($locale);
        return $this->redirect($this->generateUrl('ftfs_preferencebundle_user_preference', array(
            '_locale'=>$locale,
            'id' => $id,
        )));
    }

    public function setUserTimeZoneAction($id)
    {
        $user = $this->getDoctrine()->getEntityManager()->getRepository('FTFSUserBundle:User')->find($id);
        if(!$user) {
            throw $this->createNotFoundException('user not found !');
        }
        $timezone = $this->getRequest()->get('timezone');
        //throw new \Exception($timezone);
        if($timezone) {
            $this->get('ftfs_configurator')->set('timezone', $timezone, $user);
        }

        return $this->redirect($this->generateUrl('ftfs_preferencebundle_user_preference', array(
            'id' => $id,
        )));
    }

}
