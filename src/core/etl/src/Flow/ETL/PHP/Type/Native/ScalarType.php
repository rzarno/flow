<?php

declare(strict_types=1);

namespace Flow\ETL\PHP\Type\Native;

use Flow\ETL\PHP\Type\Type;

final class ScalarType implements NativeType
{
    public const BOOLEAN = 'boolean';

    public const FLOAT = 'float';

    public const INTEGER = 'integer';

    public const STRING = 'string';

    /**
     * @param self::* $value
     */
    private function __construct(private readonly string $value, private readonly bool $nullable)
    {
    }

    public static function boolean(bool $nullable = false) : self
    {
        return new self(self::BOOLEAN, $nullable);
    }

    public static function float(bool $nullable = false) : self
    {
        return new self(self::FLOAT, $nullable);
    }

    public static function integer(bool $nullable = false) : self
    {
        return new self(self::INTEGER, $nullable);
    }

    public static function string(bool $nullable = false) : self
    {
        return new self(self::STRING, $nullable);
    }

    public function isBoolean() : bool
    {
        return $this->value === self::BOOLEAN;
    }

    public function isEqual(Type $type) : bool
    {
        return $type instanceof self && $type->value === $this->value && $this->nullable === $type->nullable;
    }

    public function isFloat() : bool
    {
        return $this->value === self::FLOAT;
    }

    public function isInteger() : bool
    {
        return $this->value === self::INTEGER;
    }

    public function isString() : bool
    {
        return $this->value === self::STRING;
    }

    public function isValid(mixed $value) : bool
    {
        if (null === $value && $this->nullable) {
            return true;
        }

        return match ($this->value) {
            self::STRING => \is_string($value),
            self::INTEGER => \is_int($value),
            self::FLOAT => \is_float($value),
            self::BOOLEAN => \is_bool($value),
        };
    }

    public function nullable() : bool
    {
        return $this->nullable;
    }

    public function toString() : string
    {
        return ($this->nullable ? '?' : '') . $this->value;
    }

    public function type() : string
    {
        return $this->value;
    }
}
