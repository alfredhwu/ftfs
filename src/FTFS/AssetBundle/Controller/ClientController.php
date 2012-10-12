<?php

namespace FTFS\AssetBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class ClientController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/UserBundle/Company",
            'controller' => "FTFS/AssetBundle/Client",
        ));
    }

    /**
     * get entity list for index Action
     */
    protected function getEntityList()
    {
        // ToDo: add some filter here
        return $this->getDoctrine()->getEntityManager()->getRepository($this->getEntityPath())->findBy(array(
            'is_client' => true,
        ));
    }
}
