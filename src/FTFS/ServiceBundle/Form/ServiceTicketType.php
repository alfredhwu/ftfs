<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                        ->add('devices', 'collection', array(
                            'type' => new \FTFS\AssetBundle\Form\DeviceType(),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'by_reference' => false,
                        ))
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
                        ->add('company', 'entity', array(
                            'class' => 'FTFSUserBundle:Company',
                            'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                                return $er->createQueryBuilder('c')
                                            ->where('c.is_client = 1')
                                            ->orderBy('c.name', 'ASC');
                            },
                            'empty_value' => '<Select>',
                            'required' => false,
                        ))
                        ->add('requested_by', 'entity', array(
                            'class' => 'FTFSUserBundle:User',
                            'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                                return $er->createQueryBuilder('u')
                                            ->leftJoin('u.company', 'c')
                                            ->where('c.is_client = 1')
                                            ->orderBy('u.surname', 'ASC', 'u.firstname', 'ASC');
                            },
                            'empty_value' => '<Select>',
                        ))
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
                        ->add('service')
                        /*
                        ->add('asset', 'entity', array(
                            'class' => 'FTFSAssetBundle:Asset',
                            'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                                return $er->createQueryBuilder('a')
                                            ->orderBy('a.name', 'ASC');
                            },
                            'empty_value' => '<Select>',
                            'required' => false,
                        ))
                        ->add('devices', null, array(
                            'empty_value' => '<Select>',
                            'required' => false,
                        ))
                         */
                        ->add('assigned_to')
                        ->add('summary')
                        ->add('detail')
                        ->add('devices', 'collection', array(
                            'type' => new \FTFS\AssetBundle\Form\DeviceType(),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'by_reference' => false,
                        ))
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
                $builder
                    ->add('service')
                    //->add('asset')
                    //->add('devices')
                    ->add('summary')
                    ->add('detail')
                    ;
                break;
            default:
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FTFS\ServiceBundle\Entity\ServiceTicket',
        ));
    }



    public function getName()
    {
        return 'ftfs_servicebundle_serviceticket_form';
    }
}
