<?php

namespace FTFS\ServiceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ServiceTypeType extends AbstractType
{
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        if(array_key_exists('view', $this->options))
        {
            $view = $this->options['view'];
        }else{
            $view = 'default';
        } 
        if( 'edit' === $view || 'new' === $view )
        {
            $builder
                ->add('name')
                ->add('description')
            ;
        }else{
            $builder
                ->add('name', null, array(
                    'read_only' => true,
                ))
                ->add('active', null, array(
                    'read_only' => true,
                ))
                ->add('description', null, array(
                    'read_only' => true,
                ))
            ;
        }
    }

    public function getName()
    {
        return 'ftfs_servicebundle_servicetype_form';
    }
}
