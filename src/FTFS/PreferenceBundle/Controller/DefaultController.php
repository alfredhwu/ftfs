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

    public function sharelistIndexAction($id)
    {
        $user = $this->getDoctrine()->getEntityManager()->getRepository('FTFSUserBundle:User')->find($id);
        if(!$user) {
            throw $this->createNotFoundException('user not found !');
        }

        $configurator = $this->get('ftfs_configurator');
        $sharelist = $configurator->get('sharelist', $user);
        if(!$sharelist) {
            $sharelist = array();
        }

        return $this->render('FTFSPreferenceBundle:Default:sharelist_index.html.twig', array(
            'user' => $user,
            'sharelist' => $sharelist,
        ));
    }

    public function sharelistAddAction($id)
    {
        $user = $this->getDoctrine()->getEntityManager()->getRepository('FTFSUserBundle:User')->find($id);
        if(!$user) {
            throw $this->createNotFoundException('user not found !');
        }
        $configurator = $this->get('ftfs_configurator');

        $sharelist = $configurator->get('sharelist', $user);
        if(!$sharelist) {
            $sharelist = array();
        }

        $share_entry = array('sharelist' => 'entry detail');
        $form = $this->createFormBuilder($share_entry)
                ->add('email')
                ->add('title', 'choice', array(
                    'choices' => array(
                        'Ms.' => 'Ms.',
                        'Mrs.' => 'Mrs.',
                        'Mr.' => 'Mr.',
                    ),
                ))
                ->add('firstname')
                ->add('surname')
                ->getForm();

        if($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            $data = $form->getData();
            //throw new \Exception('coucou');
            $email = $data['email'];
            $title = $data['title'];
            $firstname = $data['firstname'];
            $surname = $data['surname'];
            
            if(array_key_exists($email, $sharelist)) {
                throw new \Exception('email registered!');
            }

            // flush
            $sharelist[$email] = $title.' '.$firstname.' '.$surname;
            $configurator->set('sharelist', $sharelist, $user);

            return $this->redirect($this->generateUrl(
                'ftfs_preferencebundle_default_ticket_sharelist_index', 
                array(
                    'id' => $id,
                )
            ));
        }
        return $this->render('FTFSPreferenceBundle:Default:sharelist_add.html.twig', array(
            'user' => $user,
            'sharelist' => $sharelist,
            'form' => $form->createView(),
        ));

        $sharelist = $configurator->get('sharelist', $user);
        if(!$sharelist) {
            $sharelist = array();
        }

        $share_entry = array('sharelist' => 'entry detail');
        $form = $this->createformbuilder($share_entry)
                ->add('email')
                ->add('title', 'choice', array(
                    'choices' => array(
                        'ms.' => 'ms.',
                        'mrs.' => 'mrs.',
                        'mr.' => 'mr.',
                    ),
                ))
                ->add('firstname')
                ->add('surname')
                ->getform();

        if($this->getrequest()->getmethod() === 'post') {
            $form->bindrequest($this->getrequest());
            $data = $form->getdata();
            //throw new \exception('coucou');
            $email = $data['email'];
            $title = $data['title'];
            $firstname = $data['firstname'];
            $surname = $data['surname'];
            
            if(array_key_exists($email, $sharelist)) {
                throw new \exception('email registered!');
            }

            // flush
            $sharelist[$email] = $title.' '.$firstname.' '.$surname;
            $configurator->set('sharelist', $sharelist, $user);

            return $this->redirect($this->generateurl(
                'ftfs_preferencebundle_default_ticket_sharelist_index', 
                array(
                    'id' => $id,
                )
            ));
        }
        return $this->render('ftfspreferencebundle:default:sharelist_add.html.twig', array(
            'user' => $user,
            'sharelist' => $sharelist,
            'form' => $form->createview(),
        ));
    }

    public function sharelistEditAction($id, $email)
    {
        $user = $this->getDoctrine()->getEntityManager()->getRepository('FTFSUserBundle:User')->find($id);
        if(!$user) {
            throw $this->createNotFoundException('user not found !');
        }

        $configurator = $this->get('ftfs_configurator');

        $sharelist = $configurator->get('sharelist', $user);
        if(!$sharelist) {
            throw $this->createNotFoundException('contact of email '.$email.' not found !');
        }

        $share_entry = array(
            'sharelist' => 'entry detail',
            'email' => $email,
            'name' => $sharelist[$email],
        );
        $form = $this->createFormBuilder($share_entry)
                ->add('email')
                ->add('name')
                ->getForm();

        if($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            $data = $form->getData();
            $old_email = $email;
            $email = $data['email'];
            $name = $data['name'];
            //throw new \Exception('coucou');

            // flush
            unset($sharelist[$old_email]);
            $sharelist[$email] = $name;
            $configurator->set('sharelist', $sharelist, $user);

            return $this->redirect($this->generateUrl(
                'ftfs_preferencebundle_default_ticket_sharelist_index', 
                array('id' => $id)
            ));
        }
        return $this->render('FTFSPreferenceBundle:Default:sharelist_edit.html.twig', array(
            'user' => $user,
            'sharelist' => $sharelist,
            'form' => $form->createView(),
        ));
    }

    public function sharelistRemoveAction($id, $email)
    {
        $user = $this->getDoctrine()->getEntityManager()->getRepository('FTFSUserBundle:User')->find($id);
        if(!$user) {
            throw $this->createNotFoundException('user not found !');
        }

        $configurator = $this->get('ftfs_configurator');
        $sharelist = $configurator->get('sharelist', $user);
        if(!$sharelist) {
            $sharelist = array();
        }
        if(array_key_exists($email, $sharelist)) {
            unset($sharelist[$email]);
            $configurator->set('sharelist', $sharelist, $user);
        }

        return $this->redirect($this->generateUrl('ftfs_preferencebundle_default_ticket_sharelist_index', array(
                'id' => $id,
        )));
    }
}
