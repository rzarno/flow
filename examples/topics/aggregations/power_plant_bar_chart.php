<?php

declare(strict_types=1);

use function Flow\ETL\Adapter\ChartJS\bar_chart;
use function Flow\ETL\Adapter\ChartJS\to_chartjs_file;
use function Flow\ETL\Adapter\CSV\from_csv;
use function Flow\ETL\DSL\average;
use function Flow\ETL\DSL\concat;
use function Flow\ETL\DSL\df;
use function Flow\ETL\DSL\lit;
use function Flow\ETL\DSL\max;
use function Flow\ETL\DSL\min;
use function Flow\ETL\DSL\ref;
use function Flow\ETL\DSL\refs;
use function Flow\ETL\DSL\sum;

require __DIR__ . '/../../bootstrap.php';

$df = df()
    ->read(from_csv(__FLOW_DATA__ . '/power-plant-daily.csv', delimiter: ';'))
    ->withEntry('production_kwh', ref('Produkcja(kWh)'))
    ->withEntry('consumption_kwh', ref('Zużycie(kWh)'))
    ->withEntry('date', ref('Zaktualizowany czas')->toDate('Y/m/d')->dateFormat('Y/m'))
    ->select('date', 'production_kwh', 'consumption_kwh')
    ->groupBy(ref('date'))
    ->aggregate(
        average(ref('production_kwh')),
        average(ref('consumption_kwh')),
        min(ref('production_kwh')),
        min(ref('consumption_kwh')),
        max(ref('production_kwh')),
        max(ref('consumption_kwh')),
        sum(ref('production_kwh')),
        sum(ref('consumption_kwh'))
    )

    ->withEntry('production_kwh_avg', ref('production_kwh_avg')->round(lit(2)))
    ->withEntry('consumption_kwh_avg', ref('consumption_kwh_avg')->round(lit(2)))
    ->withEntry('production_kwh_min', ref('production_kwh_min')->round(lit(2)))
    ->withEntry('consumption_kwh_min', ref('consumption_kwh_min')->round(lit(2)))
    ->withEntry('production_kwh_max', ref('production_kwh_max')->round(lit(2)))
    ->withEntry('consumption_kwh_max', ref('consumption_kwh_max')->round(lit(2)))
    ->withEntry('production_kwh_sum', ref('production_kwh_sum')->round(lit(2)))
    ->withEntry('consumption_kwh_sum', ref('consumption_kwh_sum')->round(lit(2)))
    ->withEntry('consumption', ref('consumption_kwh_sum')->divide(ref('production_kwh_sum')))
    ->withEntry('consumption', ref('consumption')->multiply(lit(100))->round(lit(2)))
    ->withEntry('consumption', concat(ref('consumption'), lit('%')))
    ->write(
        to_chartjs_file(
            bar_chart(label: ref('date'), datasets: refs(ref('production_kwh_avg'), ref('consumption_kwh_avg')))
                ->setOptions(['indexAxis' => 'y']),
            output: __FLOW_OUTPUT__ . '/power_plant_bar_chart.html'
        )
    );

if ($_ENV['FLOW_PHAR_APP'] ?? false) {
    return $df;
}

$df->run();
