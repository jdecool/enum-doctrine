<?php

declare(strict_types=1);

namespace JDecool\Enum\Doctrine\Tests\Fixtures;

use JDecool\Enum\Enum;

/**
 * @method static static MALE()
 * @method static static FEMALE()
 */
class Gender extends Enum
{
    private const MALE = 'male';
    private const FEMALE = 'female';
}
