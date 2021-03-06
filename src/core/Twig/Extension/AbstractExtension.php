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

namespace Lotgd\Core\Twig\Extension;

use Twig\Extension\AbstractExtension as AbstractExtensionCore;
use Lotgd\Core\Pattern as PatternCore;

class AbstractExtension extends AbstractExtensionCore
{
    use PatternCore\Container;
}
