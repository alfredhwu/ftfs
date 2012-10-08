<?php

namespace FTFS\AssetBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class AssetController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/AssetBundle/Asset"
        ));
    }

    protected function getEntity($action, $id = -1)
    {
        $entity = parent::getEntity($action, $id);

        switch($action) {
            case 'new':
                $entity->setInstalledAt(new \DateTime('now'));
                break;
            default:
        }
        
        return $entity;
    }
}
