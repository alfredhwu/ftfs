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
            ->add('supplier')
        ;
    }

    public function getName()
    {
        return 'ftfs_productbundle_product_form';
    }
}
