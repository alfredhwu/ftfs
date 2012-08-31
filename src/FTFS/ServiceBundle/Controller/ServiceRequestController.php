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

    /**
     * pre flush entity filter
     * do some pre flush auto settings here
     *
     */
    protected function preFlushEntity($entity, $action, $request)
    {
        switch($action)
        {
            case 'new':
                $entity->setRequestedAt(new \DateTime('now'));
                $entity->setLastModifiedAt(new \DateTime('now'));
                $entity->setStatus('20_unsent');
                break;
        }
    }

    protected function getEntityList()
    {
        return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
            array(
            ),
            array(
                'last_modified_at' => 'desc',
            )
        );
    }
}
