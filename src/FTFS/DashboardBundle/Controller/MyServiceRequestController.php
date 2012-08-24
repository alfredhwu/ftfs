<?php

namespace FTFS\DashboardBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;

class MyServiceRequestController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "/FTFS/ServiceBundle/ServiceRequest",
            'controller' => "/FTFS/ServiceBundle/ServiceRequest",
            'view' => "/FTFS/ServiceBundle/ServiceRequest",
        ));
    }

    protected function initEntity($entity)
    {
        $entity->setRequestedAt(new \DateTime('now'));
        $entity->setStatus('new');
        return $entity;
    }
}
