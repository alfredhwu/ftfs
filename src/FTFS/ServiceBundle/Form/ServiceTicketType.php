<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServiceTicketType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('severity')
            ->add('priority')
            ->add('status')
            ->add('last_modified_at', null, array(
                'widget' => 'single_text',
            ))
            ->add('requested_by')
            ->add('requested_at', null, array(
                'widget' => 'single_text',
            ))
            ->add('requested_via')
            ->add('service')
            ->add('assigned_to')
            ->add('summary')
            ->add('detail')
            ->add('asset')
            ->add('created_at', null, array(
                'widget' => 'single_text',
            ))
            ->add('opened_at', null, array(
                'widget' => 'single_text',
            ))
            ->add('closed_at', null, array(
                'widget' => 'single_text',
            ))
        ;
    }

    public function getName()
    {
        return 'ftfs_servicebundle_serviceticket_form';
    }
}
