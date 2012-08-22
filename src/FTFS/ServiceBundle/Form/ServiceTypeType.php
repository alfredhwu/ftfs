<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServiceTypeType extends AbstractType
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
                ->add('description', null, array(
                    'read_only' => true,
                ))
            ;
        }else{
            $builder
                ->add('name')
                ->add('description')
            ;
        }
    }

    public function getName()
    {
        return 'ftfs_servicebundle_servicetype_form';
    }
}
