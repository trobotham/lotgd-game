<?php

/**
 * This file is part of Legend of the Green Dragon.
 *
 * @see https://github.com/idmarinas/lotgd-game
 *
 * @license https://github.com/idmarinas/lotgd-game/blob/master/LICENSE.txt
 * @author IDMarinas
 *
 * @since 2.7.0
 */

namespace Lotgd\Core\Factory\Template;

use Interop\Container\ContainerInterface;
use Lotgd\Core\Template\Theme as TemplateTheme;
use Lotgd\Core\Twig\Extension;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Theme implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $container->get('GameConfig')['lotgd_core'] ?? [];
        $cacheDir = trim($options['cache']['base_cache_dir'] ?? 'data/cache/', '/');

        $template = new TemplateTheme([], [
            'debug' => (bool) ($options['development'] ?? false),
            //-- Used dir of cache
            'cache' => "{$cacheDir}/template",
            //-- Used in development for reload .twig templates
            'auto_reload' => (bool) ($options['development'] ?? false)
        ]);
        $template->setContainer($container);

        //-- Extension of LoTGD Core
        $template->addExtension(new Extension\GameCore($container));
        $template->addExtension(new Extension\FlashMessages($container));
        $template->addExtension(new Extension\Motd($container));
        $template->addExtension(new Extension\Navigation($container));
        $template->addExtension(new Extension\Translator($container));
        $template->addExtension(new Extension\Commentary($container));

        //-- Extension of a third party
        $template->addExtension(new \Marek\Twig\ByteUnitsExtension());

        //-- Important
        $template->prepareTheme();

        return $template;
    }

    public function createService(ServiceLocatorInterface $services, $canonicalName = null, $requestedName = null)
    {
        return $this($services, $requestedName);
    }
}
