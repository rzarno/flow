<?php

declare(strict_types=1);

namespace Flow\ETL\Pipeline;

use Flow\ETL\Extractor;
use Flow\ETL\FlowContext;
use Flow\ETL\Loader;
use Flow\ETL\Pipeline;
use Flow\ETL\Rows;
use Flow\ETL\Transformer;

final class VoidPipeline implements OverridingPipeline, Pipeline
{
    public function __construct(private readonly Pipeline $pipeline)
    {
    }

    public function add(Loader|Transformer $pipe) : self
    {
        return $this;
    }

    public function cleanCopy() : Pipeline
    {
        return new self($this->pipeline->cleanCopy());
    }

    public function closure(FlowContext $context) : void
    {
        $this->pipeline->closure($context);
    }

    public function has(string $transformerClass) : bool
    {
        return $this->pipeline->has($transformerClass);
    }

    /**
     * @return array<Pipeline>
     */
    public function pipelines() : array
    {
        $pipelines = [];

        if ($this->pipeline instanceof OverridingPipeline) {
            $pipelines = $this->pipeline->pipelines();
        }
        $pipelines[] = $this->pipeline;

        return $pipelines;
    }

    public function pipes() : Pipes
    {
        return $this->pipeline->pipes();
    }

    public function process(FlowContext $context) : \Generator
    {
        foreach ($this->pipeline->process($context) as $rows) {
            // do nothing, put those rows into void
        }

        yield new Rows();
    }

    public function setSource(Extractor $extractor) : self
    {
        return $this;
    }

    public function source() : Extractor
    {
        return $this->pipeline->source();
    }
}
