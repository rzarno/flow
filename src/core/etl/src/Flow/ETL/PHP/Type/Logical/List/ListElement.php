<?php declare(strict_types=1);

namespace Flow\ETL\PHP\Type\Logical\List;

use function Flow\ETL\DSL\type_boolean;
use function Flow\ETL\DSL\type_float;
use function Flow\ETL\DSL\type_int;
use function Flow\ETL\DSL\type_object;
use function Flow\ETL\DSL\type_string;
use Flow\ETL\PHP\Type\Logical\ListType;
use Flow\ETL\PHP\Type\Logical\MapType;
use Flow\ETL\PHP\Type\Logical\StructureType;
use Flow\ETL\PHP\Type\Type;

final class ListElement
{
    public function __construct(private readonly Type $value)
    {
    }

    public static function boolean() : self
    {
        return new self(type_boolean(false));
    }

    public static function float() : self
    {
        return new self(type_float(false));
    }

    public static function fromType(Type $type) : self
    {
        return new self($type);
    }

    public static function integer() : self
    {
        return new self(type_int(false));
    }

    public static function list(ListType $type) : self
    {
        return new self($type);
    }

    public static function map(MapType $type) : self
    {
        return new self($type);
    }

    /**
     * @param class-string $class
     */
    public static function object(string $class, bool $nullable = false) : self
    {
        return new self(type_object($class, $nullable));
    }

    public static function string() : self
    {
        return new self(type_string(false));
    }

    public static function structure(StructureType $structure) : self
    {
        return new self($structure);
    }

    public function isEqual(mixed $value) : bool
    {
        return $this->value->isEqual($value);
    }

    public function isValid(mixed $value) : bool
    {
        return $this->value->isValid($value);
    }

    public function toString() : string
    {
        return $this->value->toString();
    }

    public function type() : Type
    {
        return $this->value;
    }
}
