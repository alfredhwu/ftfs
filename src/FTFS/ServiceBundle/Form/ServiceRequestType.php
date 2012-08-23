<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServiceRequestType extends AbstractType
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
                ->add('requested_by', null, array(
                    'read_only' => true,
                ))
                ->add('requested_at', null, array(
                    'read_only' => true,
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
                ))
                ->add('last_modified_at', null, array(
                    'read_only' => true,
                    'date_widget' => 'single_text',
                    'time_widget' => 'single_text',
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
            ;
        }else{
            $builder
                ->add('name')
                ->add('requested_by')
                ->add('summary')
                ->add('detail', null, array(
                    'required' => false,
                ))
                ->add('asset_name', 'choice', array(
                    'choices' => array(
                        'frx33333' => 'frx33333',
                        'frx44444' => 'frx44444',
                    ),
                ))
            ;
        }
    }

    public function getName()
    {
        return 'ftfs_servicebundle_servicerequest_form';
    }
}
