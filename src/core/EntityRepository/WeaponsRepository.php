<?php

/**
 * This file is part of Legend of the Green Dragon.
 *
 * @see https://github.com/idmarinas/lotgd-game
 *
 * @license https://github.com/idmarinas/lotgd-game/blob/master/LICENSE.txt
 * @author IDMarinas
 *
 * @since 4.0.0
 */

namespace Lotgd\Core\EntityRepository;

use Lotgd\Core\Doctrine\ORM\EntityRepository as DoctrineRepository;
use Tracy\Debugger;

class WeaponsRepository extends DoctrineRepository
{
    /**
     * Get de max level of weapons.
     *
     * @param int $dragonKills
     *
     * @return int
     */
    public function getMaxWeaponLevel(int $dragonKills = null): int
    {
        $query = $this->createQueryBuilder('u');

        try
        {
            $query->select('MAX(u.level)');

            if ($dragonKills)
            {
                $query->where('u.level <= :lvl')
                    ->setParameters(['lvl' => $dragonKills])
                ;
            }

            return $query->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }
        catch (\Throwable $th)
        {
            Debugger::log($th);

            return 0;
        }
    }

    /**
     * Get next damage in level of weapons.
     *
     * @param int $level
     *
     * @return int
     */
    public function getNextDamageLevel(int $level): int
    {
        $query = $this->createQueryBuilder('u');

        try
        {
            return $query->select('MAX(u.damage+1)')
                ->where('u.level = :lvl')
                ->setParameters(['lvl' => $level])
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }
        catch (\Throwable $th)
        {
            Debugger::log($th);

            return 1;
        }
    }
}
