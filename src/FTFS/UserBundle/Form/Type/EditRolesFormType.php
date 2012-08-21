<?php

namespace FTFS\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\AbstractType;

class EditRolesFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('roles', 'choice', array(
            'choices' => array(
                'ROLE_ADMIN' => 'ROLE_ADMIN',
                'ROLE_AGENT' => 'ROLE_AGENT',
                'ROLE_CLIENT' => 'ROLE_CLIENT',
            ),
            'multiple' => true,
        ))        
        ;
    }

    public function getName()
    {
        return 'ftfs_user_edit_roles';
    }
}
