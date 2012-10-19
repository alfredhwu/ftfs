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
            ->add('location', null, array(
                'label' => $this->getName().'.location',
            ))
            ->add('product', 'entity', array(
                'class' => 'FTFSProductBundle:Product',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                                ->orderBy('p.name', 'ASC');
                },
                'label' => $this->getName().'.product',
            ))
            ->add('specification', null, array(
                'label' => $this->getName().'.specification',
            ))
            ->add('module_name', null, array(
                'label' => $this->getName().'.module_name',
            ))
            ->add('serial_pn', null, array(
                'label' => $this->getName().'.serial_pn',
            ))
            ->add('serial_sn', null, array(
                'label' => $this->getName().'.serial_sn',
            ))
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
