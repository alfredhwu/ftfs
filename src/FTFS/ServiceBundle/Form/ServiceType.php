<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServiceType extends AbstractType
{
    private $is_show_mode;

    public function __construct($is_show_mode=false)
    {
        $this->is_show_mode = (! $is_show_mode) ? false : true;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        if($this->is_show_mode)
        {
            $builder
                ->add('name', null, array(
                    'read_only' => true,
                ))
                ->add('type', null, array(
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
                ->add('request_received_at', null, array(
                    'read_only' => true,
                ))
                ->add('opened_at', null, array(
                    'read_only' => true,
                ))
                ->add('resolved_at', null, array(
                    'read_only' => true,
                ))
                ->add('closed_at', null, array(
                    'read_only' => true,
                ))
                ->add('last_modified_at', null, array(
                    'read_only' => true,
                ))
            ;
        }else{
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
        }
    }

    public function getName()
    {
        return 'ftfs_servicebundle_service_form';
    }
}
