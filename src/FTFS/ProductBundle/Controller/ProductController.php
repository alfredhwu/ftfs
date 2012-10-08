<?php

namespace FTFS\ProductBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class ProductController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ProductBundle/Product"
        ));
    }
}
