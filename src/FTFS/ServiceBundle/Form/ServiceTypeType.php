<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServiceTypeType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
        ;
    }

    public function getName()
    {
        return 'ftfs_servicebundle_servicetype';
    }
}
