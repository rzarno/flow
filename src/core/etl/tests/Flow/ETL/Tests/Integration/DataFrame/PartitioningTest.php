<?php declare(strict_types=1);

namespace Flow\ETL\Tests\Integration\DataFrame;

use function Flow\ETL\Adapter\Text\from_text;
use function Flow\ETL\DSL\df;
use function Flow\ETL\DSL\from_array;
use function Flow\ETL\DSL\from_rows;
use function Flow\ETL\DSL\int_entry;
use function Flow\ETL\DSL\lit;
use function Flow\ETL\DSL\partition;
use function Flow\ETL\DSL\ref;
use function Flow\ETL\DSL\row;
use function Flow\ETL\DSL\rows;
use function Flow\ETL\DSL\rows_partitioned;
use function Flow\ETL\DSL\str_entry;
use Flow\ETL\Partition;
use Flow\ETL\Rows;
use Flow\ETL\Tests\Integration\IntegrationTestCase;

final class PartitioningTest extends IntegrationTestCase
{
    public function test_partition_by() : void
    {
        $rows = df()
            ->read(from_rows(
                rows(
                    row(int_entry('id', 1), str_entry('country', 'PL'), int_entry('age', 20)),
                    row(int_entry('id', 2), str_entry('country', 'PL'), int_entry('age', 20)),
                    row(int_entry('id', 3), str_entry('country', 'PL'), int_entry('age', 25)),
                    row(int_entry('id', 4), str_entry('country', 'PL'), int_entry('age', 30)),
                    row(int_entry('id', 5), str_entry('country', 'US'), int_entry('age', 40)),
                    row(int_entry('id', 6), str_entry('country', 'US'), int_entry('age', 40)),
                    row(int_entry('id', 7), str_entry('country', 'US'), int_entry('age', 45)),
                    row(int_entry('id', 9), str_entry('country', 'US'), int_entry('age', 50)),
                )
            ))
            ->partitionBy(ref('country'))
            ->get();

        $this->assertEquals(
            [
                rows_partitioned(
                    [
                        row(int_entry('id', 1), str_entry('country', 'PL'), int_entry('age', 20)),
                        row(int_entry('id', 2), str_entry('country', 'PL'), int_entry('age', 20)),
                        row(int_entry('id', 3), str_entry('country', 'PL'), int_entry('age', 25)),
                        row(int_entry('id', 4), str_entry('country', 'PL'), int_entry('age', 30)),
                    ],
                    [
                        partition('country', 'PL'),
                    ]
                ),
                rows_partitioned(
                    [
                        row(int_entry('id', 5), str_entry('country', 'US'), int_entry('age', 40)),
                        row(int_entry('id', 6), str_entry('country', 'US'), int_entry('age', 40)),
                        row(int_entry('id', 7), str_entry('country', 'US'), int_entry('age', 45)),
                        row(int_entry('id', 9), str_entry('country', 'US'), int_entry('age', 50)),
                    ],
                    [
                        partition('country', 'US'),
                    ]
                ),
            ],
            \iterator_to_array($rows)
        );
    }

    public function test_partition_by_partitions_order() : void
    {
        df()
            ->read(from_array(
                \array_merge(...\array_map(
                    function (int $i) : array {
                        $data = [];

                        $maxItems = \random_int(2, 10);

                        for ($d = 0; $d < $maxItems; $d++) {
                            $data[] = [
                                'id' => \uniqid('', true),
                                'created_at' => (new \DateTimeImmutable('2020-01-01'))->add(new \DateInterval('P' . $i . 'D'))->setTime(\random_int(0, 23), \random_int(0, 59), \random_int(0, 59)),
                                'value' => \random_int(1, 1000),
                            ];
                        }

                        return $data;
                    },
                    \range(1, 10)
                ))
            ))
            ->withEntry('year', ref('created_at')->dateFormat('Y'))
            ->withEntry('month', ref('created_at')->dateFormat('m'))
            ->withEntry('day', ref('created_at')->dateFormat('d'))
            ->partitionBy(ref('year'), ref('day'), ref('month'))
            ->run(function (Rows $rows) : void {
                $this->assertSame(
                    [
                        'year', 'day', 'month', // order is changed on purpose
                    ],
                    \array_map(
                        fn (Partition $p) => $p->name,
                        $rows->partitions()->toArray()
                    )
                );
            });

    }

    public function test_pruning_multiple_partitions() : void
    {
        $rows = df()
            ->read(from_text(__DIR__ . '/Fixtures/Partitioning/multi_partition_pruning_test/year=*/month=*/day=*/*.txt'))
            ->filterPartitions(ref('year')->cast('int')->greaterThanEqual(lit(2023)))
            ->filterPartitions(ref('month')->cast('int')->greaterThanEqual(lit(1)))
            ->filterPartitions(ref('day')->cast('int')->lessThan(lit(3)))
            ->filter(ref('text')->notEquals(lit('dupa')))
            ->withEntry('day', ref('day')->cast('int'))
            ->collect()
            ->fetch();

        $this->assertCount(2, $rows);
        $this->assertSame([1, 2], $rows->reduceToArray('day'));
    }

    public function test_pruning_single_partition() : void
    {
        $rows = df()
            ->read(from_text(__DIR__ . '/Fixtures/Partitioning/multi_partition_pruning_test/year=*/month=*/day=*/*.txt'))
            ->filterPartitions(ref('year')->concat(lit('-'), ref('month')->strPadLeft(2, '0'), lit('-'), ref('day')->strPadLeft(2, '0'))->cast('date')->greaterThanEqual(lit(new \DateTimeImmutable('2023-01-01'))))
            ->collect()
            ->select('year')
            ->withEntry('year', ref('year')->cast('int'))
            ->groupBy(ref('year'))
            ->fetch();

        $this->assertCount(1, $rows);
        $this->assertSame(2023, $rows->first()->valueOf('year'));
    }
}
