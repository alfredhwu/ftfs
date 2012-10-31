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
        switch($role)
        {
            case 'client':
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
                break;
            case 'agent':
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
                                        ->andWhere('u.is_agent = 0')
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
                    ->add('assigned_to', 'entity', array(
                        'class' => 'FTFSUserBundle:User',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('u')
                                        ->leftJoin('u.company', 'c')
                                        ->where('u.is_agent = 1')
                                        ->andWhere('c.is_client = 0')
                                        ->andWhere('c.is_supplier = 0')
                                        ->orderBy('u.surname', 'ASC', 'u.firstname', 'ASC');
                        },
                    ))
                    ->add('summary')
                    ->add('detail')
                    ;
                    if($view === 'new') {
                        $builder->add('devices', 'collection', array(
                            'type' => new \FTFS\AssetBundle\Form\DeviceType(),
                            'allow_add' => true,
                            'allow_delete' => true,
                            'by_reference' => false,
                        ));
                    }
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
