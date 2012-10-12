<?php

namespace FTFS\ProductBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class SupplierController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/UserBundle/Company",
            'controller' => "FTFS/ProductBundle/Supplier",
        ));
    }

    /**
     * get entity list for index Action
     */
    protected function getEntityList()
    {
        // ToDo: add some filter here
        return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(array(
            'is_supplier' => true,
        ));
    }
}
