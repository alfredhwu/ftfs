<?php

namespace FTFS\AssetBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DeviceType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('local_site')
            ->add('remote_site')
            ->add('product', 'entity', array(
                'class' => 'FTFSProductBundle:Product',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                                ->orderBy('p.name', 'ASC');
                },
            ))
            ->add('module_name')
            ->add('module_pn')
            ->add('module_sn')
            ->add('specification')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FTFS\AssetBundle\Entity\Device',
        ));
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'FTFS\AssetBundle\Entity\Device',
        );
    }

    public function getName()
    {
        return 'ftfs_assetbundle_device_form';
    }
}
