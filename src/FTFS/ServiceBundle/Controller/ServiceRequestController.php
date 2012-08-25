<?php

namespace FTFS\ServiceBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;

class ServiceRequestController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ServiceBundle/ServiceRequest"
        ));
    }

    protected function initEntity($entity)
    {
        $entity->setRequestedAt(new \DateTime('now'));
        $entity->setStatus('new');
        return $entity;
    }
}
