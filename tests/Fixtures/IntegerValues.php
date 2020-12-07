<?php

declare(strict_types=1);

namespace JDecool\Enum\Doctrine\Tests\Fixtures;

use JDecool\Enum\Enum;

/**
 * @method static static ONE()
 * @method static static TWO()
 */
class IntegerValues extends Enum
{
    public const ONE = 1;
    public const TWO = 2;
}
