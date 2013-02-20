<?php

namespace BR\SignedRequestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Baldur Rensch <brensch@gmail.com>
 */
class ConfigurationCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $container->findTaggedServiceIds('br_signed_request.signing_service');
        if (empty($taggedServices)) {
            $signingService = new Reference('br_signed_request.signing_service.md5');
        } elseif (count($taggedServices) == 1) {
            $ids = array_keys($taggedServices);
            $signingService = new Reference($ids[0]);
        } else {
            throw new \InvalidArgumentException("You can only define one signing service");
        }

        $listeners = array('request', 'response');
        foreach ($listeners as $listener) {
            if ($container->getParameter('br_signed_request.' . $listener . '_listener.enabled')) {
                $definition = $container->getDefinition('br_signed_request.listener.' . $listener);

                $definition->addTag('kernel.event_listener', array(
                        'event'  => 'kernel.' . $listener,
                        'method' => 'onKernel' . ucfirst($listener),
                    )
                );

                $definition->addMethodCall('setSigningService', array($signingService));
            }
        }
    }
}
