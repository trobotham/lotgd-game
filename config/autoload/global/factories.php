<?php

use Lotgd\Core\Factory;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'service_manager' => [
        'factories' => [
            //-- LotGD factories
            Lotgd\Core\Character\Stats::class => Factory\Character\Stats::class,
            Lotgd\Core\Component\FlashMessages::class => Factory\Component\FlashMessages::class,
            Lotgd\Core\Db\Doctrine::class => Factory\Db\Doctrine::class,
            Lotgd\Core\Db\Dbwrapper::class => Factory\Db\Dbwrapper::class,
            Lotgd\Core\Installer\Install::class => Factory\Installer\Install::class,
            Lotgd\Core\Lib\Cache::class => Factory\Lib\Cache::class,
            Lotgd\Core\Lib\Settings::class => Factory\Lib\Settings::class,
            Lotgd\Core\Lib\SettingsExtended::class => Factory\Lib\SettingsExtended::class,
            Lotgd\Core\Navigation\Navigation::class => InvokableFactory::class,
            Lotgd\Core\Navigation\AccessKeys::class => InvokableFactory::class,
            Lotgd\Core\Output\Color::class => InvokableFactory::class,
            Lotgd\Core\Output\Collector::class => Factory\Output\Collector::class,
            Lotgd\Core\Output\Commentary::class => Factory\Output\Commentary::class,
            Lotgd\Core\Output\Code::class => InvokableFactory::class,
            Lotgd\Core\Output\Format::class => Factory\Output\Format::class,
            Lotgd\Core\Template\Theme::class => Factory\Template\Theme::class,
            Lotgd\Core\Translator\Translator::class => Factory\Translator\Translator::class,
            Lotgd\Core\Http::class => InvokableFactory::class,
            Zend\I18n\Translator\LoaderPluginManager::class => Factory\Translator\LoaderPluginManager::class,

            Lotgd\Core\Session::class => Factory\Session::class,

            //-- Other factories
            Zend\Session\Config\ConfigInterface::class => Zend\Session\Service\SessionConfigFactory::class,
            Zend\Session\ManagerInterface::class => Zend\Session\Service\SessionManagerFactory::class,
            Zend\Session\Storage\StorageInterface::class => Zend\Session\Service\StorageFactory::class,
        ]
    ]
];
