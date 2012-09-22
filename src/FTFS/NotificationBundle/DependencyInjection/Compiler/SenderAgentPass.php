<?php

namespace FTFS\NotificationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;;

class SenderAgentPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if(!$container->hasDefinition('ftfs_notification.sender.sender')) {
            return;
        }

        $agents = array();
        foreach($container->findTaggedServiceIds('ftfs_notification.sender.agent') as $id => $tags) {
            foreach($tags as $tag) {
                if(empty($tag['alias'])) {
                    throw new \InvalidArgumentExpression(sprintf('The Sending Agent "%s" must have an alias', $id));
                }
                $agents[$tag['alias']] = new Reference($id);
            }
        }

        $container->getDefinition('ftfs_notification.sender.sender')->replaceArgument(0, $agents);
    }
}
