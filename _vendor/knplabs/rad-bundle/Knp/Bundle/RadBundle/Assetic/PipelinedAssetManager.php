<?php

namespace Knp\Bundle\RadBundle\Assetic;

use Assetic\Factory\LazyAssetManager;

class PipelinedAssetManager extends LazyAssetManager
{
    /**
     * Return only non-pipelined assets.
     *
     * {@inheritdoc}
     */
    public function getNames()
    {
        return array_filter(parent::getNames(), function($item) {
            return false === strpos($item, '_pipeline_');
        });
    }
}
