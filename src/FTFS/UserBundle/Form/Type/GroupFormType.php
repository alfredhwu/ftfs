<?php
namespace FTFS\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class GroupFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name')
                ->add('roles', 'choice', array(
                    'choices' => array(
                        'ROLE_ADMIN' => 'ROLE_ADMIN',
                        'ROLE_AGENT' => 'ROLE_AGENT',
                        'ROLE_CLIENT' => 'ROLE_CLIENT',
                        'ROLE_USER' => 'ROLE_USER',
                    ),
                    'multiple' => true,
                )) 
        ;
    }

    public function getName()
    {
        return 'ftfs_user_group';
    }
}
