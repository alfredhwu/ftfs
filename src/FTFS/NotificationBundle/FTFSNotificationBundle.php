<?php

namespace FTFS\NotificationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FTFSNotificationBundle extends Bundle
{
    public function getParent()
    {
        return 'merkNotificationBundle';
    }    
}
