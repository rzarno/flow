<?php

declare(strict_types=1);

use function Flow\ETL\Adapter\Parquet\to_parquet;
use function Flow\ETL\DSL\df;
use function Flow\ETL\DSL\from_array;
use function Flow\ETL\DSL\overwrite;
use function Flow\ETL\DSL\ref;
use Ramsey\Uuid\Uuid;

require __DIR__ . '/../../bootstrap.php';

df()
    ->read(from_array(
        \array_merge(...\array_map(
            function (int $i) : array {
                $data = [];

                $maxItems = \random_int(2, 10);

                for ($d = 0; $d < $maxItems; $d++) {
                    $data[] = [
                        'id' => Uuid::uuid4()->toString(),
                        'created_at' => (new \DateTimeImmutable('2020-01-01'))->add(new \DateInterval('P' . $i . 'D'))->setTime(\random_int(0, 23), \random_int(0, 59), \random_int(0, 59)),
                        'value' => \random_int(1, 1000),
                    ];
                }

                return $data;
            },
            \range(1, 300)
        ))
    ))
    ->partitionBy(ref('created_at'))
    ->saveMode(overwrite())
    ->write(to_parquet(__FLOW_OUTPUT__ . '/date_partitioned'))
    ->run();
