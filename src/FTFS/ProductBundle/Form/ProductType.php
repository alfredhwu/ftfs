<?php

namespace FTFS\ProductBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('category')
            ->add('description')
            ->add('supplier', 'entity', array(
                'class' => 'FTFSUserBundle:Company',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                                ->where('c.is_supplier = :is_supplier')
                                    ->setParameter('is_supplier', true)
                                ->orderBy('c.name');
                },
            ))
        ;
    }

    public function getName()
    {
        return 'ftfs_productbundle_product_form';
    }
}
