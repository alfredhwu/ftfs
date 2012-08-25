<?php

namespace FTFS\ServiceBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class ServiceTypeController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ServiceBundle/ServiceType"
        ));
    }

    protected function initEntity($entity)
    {
        $entity->setActive(true);
        return $entity;
    }

    public function activateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository($this->getEntityPath())->find($id);

        if(!$entity)
        {
            throw $this->createNotFoundException('Entity not found !');        
        }

        $entity->setActive(!$entity->getActive());
        $em->flush($entity);
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));

    }
}
