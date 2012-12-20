<?php

namespace FTFS\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\AbstractType;

class EditFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('username')
                ->add('email', 'email', array(
                    'read_only' => true,
                ))
                ->add('title', 'choice', array(
                    'choices' => array(
                        'Ms.' => 'Ms.',
                        'Mrs.'  => 'Mrs.',
                        'Mr.'   => 'Mr.',
                    ),
                ))
                ->add('surname')
                ->add('first_name')
                ->add('company')
                ->add('address')
                ->add('office_phone')
                ->add('office_fax')
                ->add('mobile_phone')
        ;
    }

    public function getName()
    {
        return 'ftfs_user_edit';
    }
}
