<?php

namespace FTFS\ServiceBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class ServiceTypeController extends BaseController
{
    public function __construct()
    {
        parent::__construct("FTFS/ServiceBundle/ServiceType");
    }
}
