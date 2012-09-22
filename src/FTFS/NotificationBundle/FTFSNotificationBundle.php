<?php

namespace FTFS\NotificationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use FTFS\NotificationBundle\DependencyInjection\Compiler\SenderAgentPass;

class FTFSNotificationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SenderAgentPass());
    }
}
