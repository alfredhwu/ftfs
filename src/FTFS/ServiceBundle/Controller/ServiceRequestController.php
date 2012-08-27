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

    protected function postInitEntity($entity, $request)
    {
        $entity->setRequestedAt(new \DateTime('now'));
        $entity->setLastModifiedAt(new \DateTime('now'));
        $entity->setStatus('20_unsent');
        $this->get('session')->setFlash('ftfs.crud.flash.success', 'ftfs.crud.flash.created.sucess'); 
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
