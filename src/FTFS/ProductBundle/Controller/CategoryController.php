<?php

namespace FTFS\ProductBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class CategoryController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ProductBundle/Category"
        ));
    }
}
