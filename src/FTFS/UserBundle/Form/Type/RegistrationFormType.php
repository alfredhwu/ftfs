<?php

namespace FTFS\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('email', 'email')
        //      ->add('username')
                ->add('invitation', 'ftfs_user_invitation_type')
                ->add('plainPassword', 'repeated', array('type' => 'password'))
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
                ->add('address', null, array(
                    'required' => false,
                ))
                ->add('office_phone')
                ->add('office_fax', null, array(
                    'required' => false,
                ))
                ->add('mobile_phone', null, array(
                    'required' => false,
                ))
                ->add('other_phone_1', null, array(
                    'required' => false,
                ))
                ->add('other_phone_2', null, array(
                    'required' => false,
                ))
        ;
    }

    public function getName()
    {
        return 'ftfs_user_registration';
    }
}
