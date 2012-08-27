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

    protected function postInitEntity($entity, $request)
    {
        $entity->setActive(true);
        $this->get('session')->setFlash('ftfs.crud.flash.success', 'ftfs.crud.flash.created.sucess'); 
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
