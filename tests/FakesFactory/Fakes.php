<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\FakesFactory;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\MetadataList;

class Fakes
{
    public function faker(): FakerGenerator
    {
        return FakerFactory::create();
    }

    public function doMetadata(): Metadata
    {
        return new Metadata($this->faker()->uuid);
    }

    public function doMetadataList(int $howMany): MetadataList
    {
        $contents = [];
        for ($i = 0; $i < $howMany; $i++) {
            $contents[] = $this->doMetadata();
        }
        return new MetadataList($contents);
    }
}
