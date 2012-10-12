<?php

namespace FTFS\UserBundle\Controller;

use FTFS\CrudBundle\Controller\CrudController as BaseController;


class CompanyController extends BaseController
{
    public function __construct()
    {
        parent::__construct(array(
            'model' => "FTFS/UserBundle/Company"
        ));
    }
}
