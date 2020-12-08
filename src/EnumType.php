<?php

declare(strict_types=1);

namespace JDecool\Enum\Doctrine;

use Doctrine\DBAL\{
    DBALException,
    Platforms\AbstractPlatform,
    Types\Type,
};
use JDecool\Enum\Enum;

class EnumType extends Type
{
    protected string $enumClass = Enum::class;
    private string $name;

    /**
     * @param array<string|int, string> $types
     *
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public static function registerEnumTypes(array $types): void
    {
        foreach ($types as $typeName => $enumClass) {
            $typeName = is_string($typeName) ? $typeName : $enumClass;
            static::registerEnumType($typeName, $enumClass);
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public static function registerEnumType(string $typeNameOrEnumClass, ?string $enumClass = null): void
    {
        $typeName = $typeNameOrEnumClass;
        $enumClass = $enumClass ?: $typeNameOrEnumClass;

        if (!is_subclass_of($enumClass, Enum::class)) {
            $message = sprintf('Provided enum class "%s" is not valid. Enums must extend "%s".', $enumClass, Enum::class);
            throw new InvalidArgumentException($message);
        }

        self::addType($typeName, static::class);

        /** @var EnumType $type */
        $type = self::getType($typeName);
        $type->name = $typeName;
        $type->enumClass = $enumClass;
    }

    public function getName(): string
    {
        return $this->name ?: 'enum';
    }

    /**
     * @param mixed[] $column
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    /**
     * @param string|int|float $value
     * @return Enum|null
     *
     * @throws InvalidArgumentException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!$this->enumClass::isValid($value)) {
            $values = [];
            foreach ($this->enumClass::values() as $enum) {
                $values[] = $enum->getValue();
            }

            $message = sprintf(
                'The value "%s" is not valid for the enum "%s". Expected one of ["%s"]',
                $value,
                $this->enumClass,
                implode('", "', $values),
            );
            throw new InvalidArgumentException($message);
        }

        return $this->enumClass::of($value);
    }

    /**
     * @param Enum|null $value
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        return (string) $value->getValue();
    }
}
