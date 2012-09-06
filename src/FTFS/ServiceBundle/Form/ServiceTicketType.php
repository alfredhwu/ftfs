<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServiceTicketType extends AbstractType
{
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $view = $this->options['view'];
        $role = $this->options['role'];
        switch($view)
        {
            case 'new':
                if($role == 'client')
                {
                    $builder
                        ->add('severity', 'choice', array(
                            'preferred_choices' => array(300),
                            'choices' => array(
                                100 => 'Very High',
                                200 => 'High',
                                300 => 'Normal',
                                400 => 'Low',
                                500 => 'Very Low',
                            ),
                        ))
                        ->add('requested_by', 'text', array(
                            'read_only' => true,
                        ))
                        ->add('service')
                        ->add('summary')
                        ->add('detail')
                        ->add('asset')
                    ;
                }elseif($role=='agent'){
                    $builder
                        ->add('severity', 'choice', array(
                            'preferred_choices' => array(300),
                            'choices' => array(
                                100 => 'Very High',
                                200 => 'High',
                                300 => 'Normal',
                                400 => 'Low',
                                500 => 'Very Low',
                            ),
                        ))
                        ->add('priority', 'choice', array(
                            'preferred_choices' => array(300),
                            'choices' => array(
                                100 => 'Very High',
                                200 => 'High',
                                300 => 'Normal',
                                400 => 'Low',
                                500 => 'Very Low',
                            ),
                        ))
                        ->add('requested_by')
                        ->add('requested_at', null, array(
                            'widget' => 'single_text',
                        ))
                        ->add('requested_via', 'choice', array(
                            'preferred_choices' => array('telephone'),
                            'choices' => array(
                                'web' => 'Web',
                                'telephone' => 'Telephone',
                                'fax' => 'Fax',
                            ),
                        ))
                        ->add('assigned_to')
                        ->add('service')
                        ->add('summary')
                        ->add('detail')
                        ->add('asset')
                    ;
                }
                break;
            case 'edit':
            default:
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
    }

    public function getName()
    {
        return 'ftfs_servicebundle_serviceticket_form';
    }
}
