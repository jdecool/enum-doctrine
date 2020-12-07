<?php

declare(strict_types=1);

namespace JDecool\Enum\Doctrine\Tests\PHPUnit;

use Doctrine\DBAL\{
    Platforms\AbstractPlatform,
    Types\Type,
};
use JDecool\Enum\{
    Doctrine\EnumType,
    Doctrine\InvalidArgumentException,
    Doctrine\Tests\Fixtures\Action,
    Doctrine\Tests\Fixtures\CustomEnumType,
    Doctrine\Tests\Fixtures\Gender,
    Enum,
};
use PHPUnit\Framework\TestCase;
use Prophecy\{
    Argument,
    PhpUnit\ProphecyTrait,
    Prophecy\ObjectProphecy,
};
use ReflectionObject;
use stdClass;

class EnumTypeTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $platform;

    protected function setUp(): void
    {
        parent::setUp();

        $this->platform = $this->prophesize(AbstractPlatform::class);

        $typeRegistry = Type::getTypeRegistry();
        $ref = new ReflectionObject($typeRegistry);
        $instancesProp = $ref->getProperty('instances');
        $instancesProp->setAccessible(true);
        $instancesProp->setValue($typeRegistry, []);
    }

    public function testEnumTypesAreProperlyRegistered(): void
    {
        static::assertFalse(Type::hasType(Action::class));
        static::assertFalse(Type::hasType('gender'));

        EnumType::registerEnumType(Action::class);
        EnumType::registerEnumTypes([
            'gender' => Gender::class,
        ]);

        static::assertTrue(Type::hasType(Action::class));
        static::assertTrue(Type::hasType('gender'));
    }

    public function testEnumTypesAreProperlyCustomizedWhenRegistered(): void
    {
        static::assertFalse(Type::hasType(Action::class));
        static::assertFalse(Type::hasType(Gender::class));

        EnumType::registerEnumTypes([
            'gender' => Gender::class,
            Action::class,
        ]);

        /** @var Type $actionType */
        $actionType = Type::getType(Action::class);
        static::assertInstanceOf(EnumType::class, $actionType);
        static::assertEquals(Action::class, $actionType->getName());

        /** @var Type $actionType */
        $genderType = Type::getType('gender');
        static::assertInstanceOf(EnumType::class, $genderType);
        static::assertEquals('gender', $genderType->getName());
    }

    public function testRegisterInvalidEnumThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Provided enum class "%s" is not valid. Enums must extend "%s"',
            stdClass::class,
            Enum::class,
        ));

        EnumType::registerEnumType(stdClass::class);
    }

    public function testGetSQLDeclarationReturnsValueFromPlatform(): void
    {
        $this->platform->getVarcharTypeDeclarationSQL(Argument::cetera())->willReturn('declaration');

        $type = $this->getType(Gender::class);

        static::assertEquals('declaration', $type->getSQLDeclaration([], $this->platform->reveal()));
    }

    /**
     * @dataProvider provideValues
     */
    public function testConvertToDatabaseValueParsesEnum(string $typeName, $phpValue, string $expectedValue): void
    {
        $type = $this->getType($typeName);

        $actualValue = $type->convertToDatabaseValue($phpValue, $this->platform->reveal());

        static::assertEquals($expectedValue, $actualValue);
    }

    public function testConvertToDatabaseValueReturnsNullWhenNullIsProvided(): void
    {
        $type = $this->getType(Action::class);

        static::assertNull($type->convertToDatabaseValue(null, $this->platform->reveal()));
    }

    public function testConvertToPHPValueWithValidValueReturnsParsedData(): void
    {
        $type = $this->getType(Action::class);

        /** @var Action $value */
        $value = $type->convertToPHPValue(Action::CREATE, $this->platform->reveal());
        static::assertInstanceOf(Action::class, $value);
        static::assertEquals(Action::CREATE, $value->getValue());

        $value = $type->convertToPHPValue(Action::DELETE, $this->platform->reveal());
        static::assertInstanceOf(Action::class, $value);
        static::assertEquals(Action::DELETE, $value->getValue());
    }

    public function testConvertToPHPValueWithNullReturnsNull(): void
    {
        $type = $this->getType(Action::class);

        $value = $type->convertToPHPValue(null, $this->platform->reveal());
        static::assertNull($value);
    }

    public function testConvertToPHPValueWithInvalidValueThrowsException(): void
    {
        $type = $this->getType(Gender::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The value "invalid" is not valid for the enum "%s". Expected one of ["male", "female"]',
            Gender::class,
        ));

        $type->convertToPHPValue('invalid', $this->platform->reveal());
    }

    public function testUsingChildCustomEnumTypeRegisteredValueIsCorrect(): void
    {
        CustomEnumType::registerEnumType(Action::class);
        $type = CustomEnumType::getType(Action::class);

        static::assertInstanceOf(CustomEnumType::class, $type);
        static::assertEquals(
            'ENUM("create", "read", "update", "delete") COMMENT "JDecool\Enum\Doctrine\Tests\Fixtures\Action"',
            $type->getSQLDeclaration([], $this->platform->reveal()),
        );
    }

    public function testSQLCommentHintIsAlwaysRequired(): void
    {
        $type = $this->getType(Gender::class);

        static::assertTrue($type->requiresSQLCommentHint($this->platform->reveal()));
    }

    public function provideValues(): iterable
    {
        yield [Action::class, Action::CREATE(), Action::CREATE];
        yield [Action::class, Action::READ(), Action::READ];
        yield [Action::class, Action::UPDATE(), Action::UPDATE];
        yield [Action::class, Action::DELETE(), Action::DELETE];
        yield [Gender::class, Gender::FEMALE(), 'female'];
        yield [Gender::class, Gender::MALE(), 'male'];
    }

    private function getType(string $typeName): EnumType
    {
        EnumType::registerEnumType($typeName);

        return Type::getType($typeName);
    }
}
