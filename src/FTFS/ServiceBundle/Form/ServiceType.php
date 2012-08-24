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
        if( 'edit' === $view || 'new' === $view )
        {
            $builder
                ->add('name')
                ->add('type')
                ->add('severity', 'choice', array(
                    'choices' => array(
                        '1/5' => '1/5',
                        '2/5' => '2/5',
                        '3/5 normal' => '3/5 normal',
                        '4/5' => '4/5',
                        '5/5' => '5/5',
                    ),
                ))
                ->add('priority', 'choice', array(
                    'choices' => array(
                        '1/5' => '1/5',
                        '2/5' => '2/5',
                        '3/5 normal' => '3/5 normal',
                        '4/5' => '4/5',
                        '5/5' => '5/5',
                    ),
                ))
            ;
        }else{
            $builder
                ->add('name', null, array(
                    'read_only' => true,
                ))
                ->add('type', 'text', array(
                    'read_only' => true,
                ))
                ->add('severity', null, array(
                    'read_only' => true,
                ))
                ->add('priority', null, array(
                    'read_only' => true,
                ))
                ->add('status', null, array(
                    'read_only' => true,
                ))
                ->add('assigned_to', null, array(
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
                ->add('last_modified_at', null, array(
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
