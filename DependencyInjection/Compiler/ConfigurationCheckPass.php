<?php

namespace BR\SignedRequestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class ConfigurationCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $listeners = array('request', 'response');
        foreach ($listeners as $listener) {
            if ($enabled = $container->getParameter('br_signed_request.' . $listener . '_listener.enabled')) {
                $definition = $container->getDefinition('br_signed_request.listener.' . $listener);

                $definition->addTag('kernel.event_listener', array(
                        'event'  => 'kernel.' . $listener,
                        'method' => 'onKernel' . ucfirst($listener),
                    )
                );
            }
        }
    }
}
