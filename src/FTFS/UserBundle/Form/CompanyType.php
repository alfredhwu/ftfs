<?php

namespace FTFS\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CompanyType extends AbstractType
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
        $type = $this->getOption('type');

        $builder
            ->add('name')
            ->add('code')
            ->add('description')
            ;

        if(!$type) {
            $builder
            ->add('is_client', 'choice', array(
                'choices' => array(
                    1 => 'Yes',
                    0 => 'No',
                )
            ))
            ->add('is_supplier', 'choice', array(
                'choices' => array(
                    1 => 'Yes',
                    0 => 'No',
                )
            ))
            ;
        }
    }

    public function getName()
    {
        return 'ftfs_userbundle_company_form';
    }
}
