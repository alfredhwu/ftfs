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

    private function getOption($index)
    {
        if(array_key_exists($index, $this->options)) {
            return $this->options[$index];
        }else{
            return null;
        }
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $view = $this->getOption('view');
        $role = $this->getOption('role');
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
                                'telephone' => 'Telephone',
                                'email' => 'Email',
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
                $builder
                    ->add('name', null, array(
                        'read_only' => true,
                    ))
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
                    ;
                if($role == 'client')
                {
                    $builder
                        ->add('requested_by', 'text', array(
                            'read_only' => true,
                        ))
                    ;
                }elseif($role=='agent'){
                    $builder
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
                            'read_only' => true,
                            'widget' => 'single_text',
                        ))
                        ->add('requested_via', 'text', array(
                            'read_only' => true,
                        ))
                        ->add('assigned_to')
                    ;
                }
                break;
        }
        $builder
            ->add('service')
            ->add('summary')
            ->add('detail')
            ->add('asset')
            ;
    }

    public function getName()
    {
        return 'ftfs_servicebundle_serviceticket_form';
    }
}
