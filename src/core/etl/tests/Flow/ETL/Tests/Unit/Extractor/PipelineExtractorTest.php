<?php

declare(strict_types=1);

namespace Flow\ETL\Tests\Unit\Extractor;

use function Flow\ETL\DSL\int_entry;
use Flow\ETL\Config;
use Flow\ETL\Extractor\PipelineExtractor;
use Flow\ETL\Extractor\ProcessExtractor;
use Flow\ETL\FlowContext;
use Flow\ETL\Pipeline\SynchronousPipeline;
use Flow\ETL\Row;
use Flow\ETL\Rows;
use PHPUnit\Framework\TestCase;

final class PipelineExtractorTest extends TestCase
{
    public function test_pipeline_extractor() : void
    {
        $pipeline = new SynchronousPipeline();
        $pipeline->setSource(new ProcessExtractor(
            new Rows(Row::create(int_entry('id', 1)), Row::create(int_entry('id', 2))),
            new Rows(Row::create(int_entry('id', 3)), Row::create(int_entry('id', 4))),
            new Rows(Row::create(int_entry('id', 5)), Row::create(int_entry('id', 6))),
        ));

        $extractor = new PipelineExtractor($pipeline, Config::default());

        $this->assertCount(
            3,
            \iterator_to_array($extractor->extract(new FlowContext(Config::default())))
        );
    }
}
