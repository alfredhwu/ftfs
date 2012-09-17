<?php

namespace FTFS\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileFormType extends BaseType
{
    protected function buildUserForm(FormBuilder $builder, array $options)
    {
        $builder->add('email', 'email', array(
                    'read_only' => true,
                ))
                //->add('username')
                ->add('title', 'choice', array(
                    'choices' => array(
                        'Ms.' => 'Ms.',
                        'Mrs.'  => 'Mrs.',
                        'Mr.'   => 'Mr.',
                    ),
                ))
                ->add('surname')
                ->add('first_name')
                ->add('company', 'choice', array(
                    'choices' => array(
                        'Fujitsu Telecom France SAS' => 'Fujitsu Telecom France SAS',
                        'Orange France' => 'Orange France',
                        'Bouygue' => 'Bouygue',
                        'SUN MicroSystem' => 'SUN MicroSystem',
                        'Fujitsu Japon' => 'Fujitsu Japon',
                    ),
                ))
                ->add('address')
                ->add('office_phone')
                ->add('office_fax')
                ->add('mobile_phone')
                ->add('other_phone_1')
                ->add('other_phone_2')
        ;
    }

    public function getName()
    {
        return 'ftfs_user_profile';
    }
}
