<?php

namespace FTFS\ServiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class SelecterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('active')
            ->add('description')
        ;
    }

    public function getName()
    {
        return 'ftfs_servicebundle_service_form';
    }
}
