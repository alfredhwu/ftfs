<?php

namespace FTFS\AssetBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class ClientController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/AssetBundle/Client"
        ));
    }
}
