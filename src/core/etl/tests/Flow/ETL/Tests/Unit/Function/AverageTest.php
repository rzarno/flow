<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Function;

use function Flow\ETL\DSL\average;
use function Flow\ETL\DSL\int_entry;
use function Flow\ETL\DSL\null_entry;
use function Flow\ETL\DSL\ref;
use function Flow\ETL\DSL\str_entry;
use function Flow\ETL\DSL\window;
use Flow\ETL\Row;
use Flow\ETL\Rows;
use PHPUnit\Framework\TestCase;

final class AverageTest extends TestCase
{
    public function test_aggregation_average_from_numeric_values() : void
    {
        $aggregator = average(ref('int'));

        $aggregator->aggregate(Row::create(str_entry('int', '10')));
        $aggregator->aggregate(Row::create(str_entry('int', '20')));
        $aggregator->aggregate(Row::create(str_entry('int', '30')));
        $aggregator->aggregate(Row::create(str_entry('int', '25')));
        $aggregator->aggregate(Row::create(null_entry('not_int')));

        $this->assertSame(
            21.25,
            $aggregator->result()->value()
        );
    }

    public function test_aggregation_average_including_null_value() : void
    {
        $aggregator = average(ref('int'));

        $aggregator->aggregate(Row::create(int_entry('int', 10)));
        $aggregator->aggregate(Row::create(int_entry('int', 20)));
        $aggregator->aggregate(Row::create(int_entry('int', 30)));
        $aggregator->aggregate(Row::create(null_entry('int')));

        $this->assertSame(
            20,
            $aggregator->result()->value()
        );
    }

    public function test_aggregation_average_with_float_result() : void
    {
        $aggregator = average(ref('int'));

        $aggregator->aggregate(Row::create(int_entry('int', 10)));
        $aggregator->aggregate(Row::create(int_entry('int', 20)));
        $aggregator->aggregate(Row::create(int_entry('int', 30)));
        $aggregator->aggregate(Row::create(int_entry('int', 25)));

        $this->assertSame(
            21.25,
            $aggregator->result()->value()
        );
    }

    public function test_aggregation_average_with_integer_result() : void
    {
        $aggregator = average(ref('int'));

        $aggregator->aggregate(Row::create(int_entry('int', 10)));
        $aggregator->aggregate(Row::create(int_entry('int', 20)));
        $aggregator->aggregate(Row::create(int_entry('int', 30)));
        $aggregator->aggregate(Row::create(int_entry('int', 40)));

        $this->assertSame(
            25,
            $aggregator->result()->value()
        );
    }

    public function test_aggregation_average_with_zero_result() : void
    {
        $aggregator = average(ref('int'));

        $this->assertSame(
            0,
            $aggregator->result()->value()
        );
    }

    public function test_window_function_average_on_partitioned_rows() : void
    {
        $rows = new Rows(
            $row1 = Row::create(int_entry('id', 1), int_entry('value', 1)),
            Row::create(int_entry('id', 2), int_entry('value', 100)),
            Row::create(int_entry('id', 3), int_entry('value', 25)),
            Row::create(int_entry('id', 4), int_entry('value', 64)),
            Row::create(int_entry('id', 5), int_entry('value', 23)),
        );

        $avg = average(ref('value'))->over(window()->orderBy(ref('value')));

        $this->assertSame(42.6, $avg->apply($row1, $rows));
    }
}
