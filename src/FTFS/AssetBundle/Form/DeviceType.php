<?php

namespace FTFS\AssetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class DeviceType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('product')
            ->add('name')
            ->add('installed_at', null, array(
                'widget' => 'single_text',
            ))
            ->add('serial')
            ->add('observation')
            //->add('asset')
        ;
    }

    public function getName()
    {
        return 'ftfs_assetbundle_devicetype';
    }
}
