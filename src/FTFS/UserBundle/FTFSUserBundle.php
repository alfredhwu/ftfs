<?php

namespace FTFS\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FTFSUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }    
}
