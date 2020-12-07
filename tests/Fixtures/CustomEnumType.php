<?php

declare(strict_types=1);

namespace JDecool\Enum\Doctrine\Tests\Fixtures;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use JDecool\Enum\Doctrine\EnumType;

class CustomEnumType extends EnumType
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $values = [];
        foreach ($this->enumClass::values() as $enum) {
            $values[] = $enum->getValue();
        }

        return sprintf(
            'ENUM("%s") COMMENT "%s"',
            implode('", "', $values),
            $this->getName(),
        );
    }
}
