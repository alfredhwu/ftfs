<?php

namespace FTFS\DashboardBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;


class ServiceRequestType extends AbstractType
{
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        if(array_key_exists('view', $this->options))
        {
            $view = $this->options['view'];
        }else{
            $view = 'default';
        } 
        if( 'edit' === $view )
        {
            $builder
                ->add('name')
                ->add('severity', 'choice', array(
                    'choices' => array(
                        100 => 'Very High',
                        200 => 'High',
                        300 => 'Normal',
                        400 => 'Low',
                        500 => 'Very Low',
                    ),
                ))
                ->add('service_requested', null, array(
                    'empty_value' => 'Other service',
                    'required' => false,
                ))
                ->add('status', null, array(
                    'read_only' => true,
                ))
                ->add('last_modified_at', null, array(
                    'read_only' => true,
                    'widget' => 'single_text',
                ))
                ->add('requested_by', null, array(
                    'read_only' => true,
                ))
                ->add('requested_at', null, array(
                    'read_only' => true,
                    'widget' => 'single_text',
                ))
                ->add('requested_via', 'choice', array(
                    'empty_value' => 'Not speciated',
                    'preferred_choices' => array('Web'),
                    'required' => false,
                    'choices' => array(
                        'Web' => 'Web',
                        'Telephone' => 'Telephone',
                    ),
                ))
                ->add('asset_name', 'choice', array(
                    'empty_value' => 'Not speciated',
                    'required' => false,
                    'choices' => array(
                        'frx33333' => 'frx33333',
                        'frx44444' => 'frx44444',
                    ),
                ))
                ->add('summary')
                ->add('detail', null, array(
                    'required' => false,
                ))
            ;
        }elseif( 'new' === $view ){
            $builder
                ->add('name')
                ->add('severity', 'choice', array(
                    'choices' => array(
                        100 => 'Very High',
                        200 => 'High',
                        300 => 'Normal',
                        400 => 'Low',
                        500 => 'Very Low',
                    ),
                    'preferred_choices' => array(300),
                ))
                //->add('requested_by')
                ->add('service_requested', null, array(
                    'empty_value' => 'Other service',
                    'required' => false,
                ))
                ->add('requested_via', 'choice', array(
                    'empty_value' => 'Not speciated',
                    'preferred_choices' => array('Web'),
                    'required' => false,
                    'choices' => array(
                        'Web' => 'Web',
                        'Telephone' => 'Telephone',
                    ),
                ))
                ->add('asset_name', 'choice', array(
                    'empty_value' => 'Not speciated',
                    'required' => false,
                    'choices' => array(
                        'frx33333' => 'frx33333',
                        'frx44444' => 'frx44444',
                    ),
                ))
                ->add('summary')
                ->add('detail', null, array(
                    'required' => false,
                ))
            ;
        }else{
            $builder
                ->add('name', null, array(
                    'read_only' => true,
                ))
                ->add('severity', null, array(
                    'read_only' => true,
                ))
                ->add('service_requested', 'text', array(
                    'read_only' => true,
                ))
                ->add('status', null, array(
                    'read_only' => true,
                ))
                ->add('last_modified_at', null, array(
                    'read_only' => true,
                    'widget' => 'single_text',
                ))
                ->add('requested_by', null, array(
                    'read_only' => true,
                ))
                ->add('requested_at', null, array(
                    'read_only' => true,
                    'widget' => 'single_text',
                ))
                ->add('requested_via', null, array(
                    'read_only' => true,
                ))
                ->add('assigned_to', null, array(
                    'read_only' => true,
                ))
                ->add('asset_name', null, array(
                    'read_only' => true,
                ))
                ->add('service_deployed', 'text', array(
                    'read_only' => true,
                ))
                ->add('summary', null, array(
                    'read_only' => true,
                ))
                ->add('detail', null, array(
                    'read_only' => true,
                ))
            ;
        }
    }

    public function getName()
    {
        return 'ftfs_servicebundle_servicerequest_form';
    }
}
