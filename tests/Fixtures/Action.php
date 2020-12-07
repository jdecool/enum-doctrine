<?php

declare(strict_types=1);

namespace JDecool\Enum\Doctrine\Tests\Fixtures;

use JDecool\Enum\Enum;

/**
 * @method static static CREATE()
 * @method static static READ()
 * @method static static UPDATE()
 * @method static static DELETE()
 */
class Action extends Enum
{
    public const CREATE = 'create';
    public const READ = 'read';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
}
