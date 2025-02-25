<?php

declare(strict_types=1);

namespace Flow\ETL\Row\Schema\Constraint;

use Flow\ETL\Row\Entry;
use Flow\ETL\Row\Schema\Constraint;

final class NotEmpty implements Constraint
{
    public function __construct()
    {
    }

    public function isSatisfiedBy(Entry $entry) : bool
    {
        return match ($entry::class) {
            Entry\ArrayEntry::class,
            Entry\StructureEntry::class,
            Entry\ListEntry::class => 0 !== \count($entry->value()),
            Entry\StringEntry::class => $entry->value() !== '',
            Entry\JsonEntry::class => !\in_array($entry->value(), ['', '[]', '{}'], true),
            default => true, // everything else can't be empty
        };
    }
}
