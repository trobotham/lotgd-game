<?php

/**
 * This file is part of Legend of the Green Dragon.
 *
 * @author IDMarinas
 */

namespace Lotgd\Core\Factory\Db;

use DoctrineORMModule\Service\EntityManagerFactory;
use Interop\Container\ContainerInterface;
use Lotgd\Core\Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class Doctrine extends EntityManagerFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $container->get('GameConfig')['lotgd_core'];
        $isDevelopment = (bool) ($options['development'] ?? false);

        /* @var $options DoctrineORMModuleEntityManager */
        $options = $this->getOptions($container, 'entitymanager');
        $connection = $container->get($options->getConnection());
        $config = $container->get($options->getConfiguration());

        // initializing the resolver
        // @todo should actually attach it to a fetched event manager here, and not
        //       rely on its factory code
        $container->get($options->getEntityResolver());

        //-- Entity Manager of Doctrine
        $entityManager = DoctrineEntityManager::create($connection, $config);

        //-- Add Sql requests made by Doctrine in the Tracy debugger bar.
        if ($isDevelopment)
        {
            \MacFJA\Tracy\DoctrineSql::init($entityManager);
        }

        return $entityManager;
    }

    public function createService(ServiceLocatorInterface $services, $canonicalName = null, $requestedName = null)
    {
        return $this($services, $requestedName);
    }
}
