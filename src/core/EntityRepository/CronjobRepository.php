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

class CronjobRepository extends DoctrineRepository
{
    use Character\Bio;
    use Character\Clan;
    use Character\Setting;

    /**
     * Get character's name from an account ID.
     *
     * @param int $acctId
     *
     * @return string
     */
    public function getCharacterNameFromAcctId(int $acctId): ?string
    {
        $qb = $this->createQueryBuilder('u');

        try
        {
            return $qb->select('u.name')
                ->where('u.acct = :acct')
                ->setParameters(['acct' => $acctId])
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }
        catch (\Throwable $th)
        {
            Debugger::log($th);

            return null;
        }
    }
}
