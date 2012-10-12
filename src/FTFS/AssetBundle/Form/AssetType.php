<?php

namespace FTFS\AssetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class AssetType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('client', 'entity', array(
                'class' => 'FTFSUserBundle:Company',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                                ->where('c.is_client = :is_client')
                                    ->setParameter('is_client', true)
                                ->orderBy('c.name');
                },
            ))
            ->add('installed_in')
            ->add('name')
        ;
    }

    public function getName()
    {
        return 'ftfs_assetbundle_asset_form';
    }
}
