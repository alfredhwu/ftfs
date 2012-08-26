<?php

namespace FTFS\DashboardBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;

class MyServiceRequestController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/ServiceBundle/ServiceRequest",
            'controller' => "FTFS/DashboardBundle/MyServiceRequest",
        ));
    }

    protected function postInitEntity($entity, $request)
    {
        $entity->setRequestedBy($this->get('security.context')->getToken()->getUser());
        $session = $this->get('session');

        $init_mode = $request->get('init_mode');
        if('send'==$init_mode)
        {
            $entity->setStatus('awaiting');
            $session->setFlash('ftfs.crud.flash.success', 'ftfs.dashboardbundle.myservicerequest.form.action.new.save.send.success.flash');
        }else{
            $entity->setStatus('unsent');
            $session->setFlash('ftfs.crud.flash.success', 'ftfs.dashboardbundle.myservicerequest.form.action.new.save.nosend.success.flash');
        }
    }

    protected function postUpdateEntity($entity, $request)
    {
        $entity->setLastModifiedAt(new \DateTime('now'));
        $session = $this->get('session');
        
        $update_mode = $request->get('update_mode');
        if('send'==$update_mode)
        {
            $entity->setRequestedAt(new \DateTime('now'));
            $entity->setStatus('awaiting');
            $session->setFlash('ftfs.crud.flash.success', 'ftfs.dashboardbundle.myservicerequest.form.edit.update.send.success.flash');
        }else{
            $session->setFlash('ftfs.crud.flash.success', 'ftfs.dashboardbundle.myservicerequest.form.edit.update.nosend.success.flash');
        }
    }

    protected function getEntityType(array $options)
    {
        $entityTypeClass = '\FTFS\DashboardBundle\Form\\'.$this->namespaces['model']['entity'].'Type';
        return new $entityTypeClass($options);
    }

    protected function getEntityList()
    {
        $status = $this->getRequest()->query->get('status');
        if($status)
        {
            return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
                array(
                    'requested_by' => $this->get('security.context')->getToken()->getUser(),
                    'status' => $status,
                ),
                array(
                    'name' => 'asc',
                )
            );
        }
        return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(
            array(
                'requested_by' => $this->get('security.context')->getToken()->getUser(),
            ),
            array(
                'name' => 'asc',
            )
        );
    }
}
