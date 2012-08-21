<?php

namespace FTFS\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\AbstractType;

class EditGroupFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('group')        
        ;
    }

    public function getName()
    {
        return 'ftfs_user_edit_group';
    }
}
