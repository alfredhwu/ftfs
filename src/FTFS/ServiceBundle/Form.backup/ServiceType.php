<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServiceType extends AbstractType
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
        switch($view)
        {
        case 'edit':
            $builder
                ->add('name', null, array(
                    'read_only' => true,
                ))
                ->add('type')
                ->add('severity', 'choice', array(
                    'choices' => array(
                        100 => 'very urgent',
                        200 => 'urgent',
                        300 => 'normal',
                    ),
                ))
                ->add('priority', 'choice', array(
                    'choices' => array(
                        200 => 'superior',
                        300 => 'normal',
                        400 => 'inferior',
                    ),
                ))
                ->add('assigned_to', null, array(
                    'read_only' => true,
                ))
                ->add('summary', null, array(
                    'read_only' => true,
                ))
                ->add('detail', null, array(
                    'read_only' => true,
                ))
                ->add('asset_name')
                ->add('requested_by', null, array(
                    'read_only' => true,
                ))
                ->add('requested_via', null, array(
                    'read_only' => true,
                ))
                ->add('request_received_at', null, array(
                    'widget' => 'single_text',
                    'read_only' => true,
                ))
                ->add('opened_at', null, array(
                    'widget' => 'single_text',
                    'read_only' => true,
                ))
                ->add('resolved_at', null, array(
                    'widget' => 'single_text',
                    'read_only' => true,
                ))
                ->add('closed_at', null, array(
                    'widget' => 'single_text',
                    'read_only' => true,
                ))
            ;
            break;
        case 'new':
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
                ->add('priority', 'choice', array(
                    'choices' => array(
                        100 => 'Very High',
                        200 => 'High',
                        300 => 'Normal',
                        400 => 'Low',
                        500 => 'Very Low',
                    ),
                    'preferred_choices' => array(300),
                ))
                ->add('type')
                ->add('requested_by')
                ->add('requested_via')
                ->add('summary')
                ->add('detail')
                ->add('asset_name')
            ;
            break;
        default:
            $builder
                ->add('name', null, array(
                    'read_only' => true,
                ))
                ->add('type', 'text', array(
                    'read_only' => true,
                ))
                ->add('requested_by', null, array(
                    'read_only' => true,
                ))
                ->add('requested_via', null, array(
                    'read_only' => true,
                ))
                ->add('summary', null, array(
                    'read_only' => true,
                ))
                ->add('detail', null, array(
                    'read_only' => true,
                ))
                ->add('asset_name', null, array(
                    'read_only' => true,
                ))
                ->add('status', null, array(
                    'read_only' => true,
                ))
                ->add('last_modified_at', null, array(
                    'widget' => 'single_text',
                    'read_only' => true,
                ))
                ->add('assigned_to', null, array(
                    'read_only' => true,
                ))
                ->add('severity', null, array(
                    'read_only' => true,
                ))
                ->add('priority', null, array(
                    'read_only' => true,
                ))
                ->add('request_received_at', null, array(
                    'widget' => 'single_text',
                    'read_only' => true,
                ))
                ->add('opened_at', null, array(
                    'widget' => 'single_text',
                    'read_only' => true,
                ))
                ->add('resolved_at', null, array(
                    'widget' => 'single_text',
                    'read_only' => true,
                ))
                ->add('closed_at', null, array(
                    'widget' => 'single_text',
                    'read_only' => true,
                ))
            ;
        }
    }

    public function getName()
    {
        return 'ftfs_servicebundle_service_form';
    }
}
