<?php

namespace FTFS\ServiceBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class ServiceController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ServiceBundle/Service"
        ));
    }

    protected function preFlushEntity($entity, $action, $request)
    {
        switch($action)
        {
            case 'new':
                $entity->setRequestReceivedAt(new \DateTime('now'));
                $entity->setOpenedAt(new \DateTime('now'));
                $entity->setLastModifiedAt(new \DateTime('now'));
                $entity->setStatus("opened");
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
