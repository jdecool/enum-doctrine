Doctrine Enum Type
==================

This package provides a base implementation to define doctrine entity column types that are mapped to `JDecool\Enum\Enum` objects (of [`jdecool/enum`](https://github.com/jdecool/enum) package).

This is a port of [`acelaya/doctrine-enum-type`](https://github.com/acelaya/doctrine-enum-type).

## Installation

Install it using [Composer](https://getcomposer.org):

```bash
composer require jdecool/enum-doctrine
```

## Usage

This package provides a `JDecool\Enum\Doctrine\EnumType` class that extends `Doctrine\DBAL\Types\Type`. You can use it to easily map type names to concrete Enums.

The `EnumType` class will be used as the doctrine type for every property that is an enumeration.

```php
use JDecool\Enum\Enum;

class MyEnum extends Enum
{
    public const ENUM_1 = 'value_1';
    protected const ENUM_2 = 'value_2';
    private const ENUM_3 = 'value_3';
}
```

Then, you can map the enum to your entity.

```php
class User
{
    // ...

    /**
     * @var MyEnum
     *
     * @ORM\Column(type=MyEnum::class, length=10)
     */
    protected $action;

    // ...
}
```

The column type of the property is the FQCN of the `MyEnum` enum. To get this working, you have to register the concrete column types, using the `JDecool\Enum\Doctrine\EnumType::registerEnumType` static method.

```php
// in bootstrapping code
use JDecool\Enum\Doctrine\EnumType;

EnumType::registerEnumType(MyEnum::class);

// Don't forget to register the enums for schema operations
$platform = $em->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('VARCHAR', MyEnum::class);
```

Alternatively you can use the `JDecool\Enum\Doctrine\EnumType::registerEnumTypes`, which expects an array of enums to register.

```php
// ...

use JDecool\Enum\Doctrine\EnumType;

EnumType::registerEnumTypes([
    MyEnum::class,
    'php_enum_type' => MyEnum::class,
]);
```

If you use Doctrine with Symfony:

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        types:
            uuid: JDecool\Enum\Doctrine\EnumType
```
