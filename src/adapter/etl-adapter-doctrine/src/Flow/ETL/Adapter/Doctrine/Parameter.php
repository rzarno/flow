<?php

declare(strict_types=1);

namespace Flow\ETL\Adapter\Doctrine;

use Doctrine\DBAL\ArrayParameterType;
use Flow\ETL\Row\EntryReference;
use Flow\ETL\Rows;

final class Parameter implements QueryParameter
{
    public function __construct(
        public readonly string $queryParamName,
        public readonly EntryReference $ref,
        public readonly int $type = ArrayParameterType::STRING,
    ) {
    }

    public static function asciis(string $queryParamName, EntryReference $ref) : self
    {
        return new self($queryParamName, $ref, ArrayParameterType::ASCII);
    }

    public static function ints(string $queryParamName, EntryReference $ref) : self
    {
        return new self($queryParamName, $ref, ArrayParameterType::INTEGER);
    }

    public static function strings(string $queryParamName, EntryReference $ref) : self
    {
        return new self($queryParamName, $ref, ArrayParameterType::STRING);
    }

    public function queryParamName() : string
    {
        return $this->queryParamName;
    }

    public function toQueryParam(Rows $rows) : mixed
    {
        return $rows->reduceToArray($this->ref);
    }

    public function type() : ?int
    {
        return $this->type;
    }
}
