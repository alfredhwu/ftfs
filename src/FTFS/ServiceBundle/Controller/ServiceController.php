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

    public function getServiceListByJSONAction()
    {
        $entities = $this->getDoctrine()->getEntityManager()->getRepository('FTFSServiceBundle:Service')->findAll();
        $list = array();
        foreach($entities as $entity) {
            $list[$entity->getName()] = $entity->getId();
        }
        $response = new \Symfony\Component\HttpFoundation\Response(json_encode($list));
        return $response;
    }

    /**
     * init new create entity
     * for new action only
     */
    protected function initNewEntity($entity)
    {
        $entity->setActive(true);
    }

    /**
     * activate a service type
     * for integrity reason, the delete of an entity is depreciated
     * use deactivate instead
     */
    public function activateAction($id)
    {
        $entity = $this->getEntity('activate', $id);
        $entity->setActive(!$entity->getActive());

        $em = $this->getDoctrine()->getEntityManager();
        $em->flush($entity);

        $this->notify('activate');
        return $this->redirect($this->generateUrl($this->getRoutingPrefix().'_index'));
    }



    public function showAction($id)
    {
        $entity = $this->getEntity('show', $id);
        $form = $this->createForm(new \FTFS\ServiceBundle\Form\ServiceType, $entity);
        return $this->render($this->getViewPath().':show.html.twig', array(
            'entity' => $entity,
            'meta' => $this->getMeta(),
            'prefix' => $this->getRoutingPrefix(),
            'form' => $form->createView(),
        ));
    }
}
