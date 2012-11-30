<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('active')
            ->add('open_to_client')
            ->add('description')
        ;
    }

    public function getName()
    {
        return 'ftfs_servicebundle_service_form';
    }
}
