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

namespace Lotgd\Core\EntityRepository\Account;

use Lotgd\Core\Entity as LotgdEntity;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Functions for login user.
 */
trait Login
{
    /**
     * Process login and get data.
     *
     * @param string $login
     * @param string $password
     *
     * @return array
     */
    public function processLoginGetAcctData(string $login, string $password)
    {
        $qb = $this->createQueryBuilder('u');

        try
        {
            $data = $qb->addSelect('ep')
                ->where('u.login = :login AND u.password = :password AND u.locked = :locked')
                ->setParameters([
                    'login' => $login,
                    'password' => $password,
                    'locked' => false
                ])
                ->leftJoin(LotgdEntity\AccountsEverypage::class, 'ep', Join::WITH, $qb->expr()->eq('ep.acctid', 'u.acctid'))
                ->getQuery()
                ->getResult()
            ;
        }
        catch (\Throwable $th)
        {
            \Tracy\Debugger::log($th);

            return null;
        }

        //-- Fail if not found
        if (0 == count($data))
        {
            return null;
        }

        return $this->processUserData($data);
    }

}