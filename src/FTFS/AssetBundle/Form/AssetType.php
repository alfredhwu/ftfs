<?php

namespace FTFS\AssetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class AssetType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('client')
            ->add('installed_in')
            ->add('name')
        ;
    }

    public function getName()
    {
        return 'ftfs_assetbundle_asset_form';
    }
}
