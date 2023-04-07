<?php

declare(strict_types=1);

namespace EtaOrionis\ComposerJsonManipulator\Tests\ComposerJsonSchemaValidation;

use EtaOrionis\ComposerJsonManipulator\ComposerJson;
use PHPUnit\Framework\TestCase;
use EtaOrionis\ComposerJsonManipulator\Helpers\Section;

final class ComposerJsonSchemaValidationTest extends TestCase
{
    public function testCheckEmptyKeysAreRemoved(): void
    {
        $sourceJsonPath = __DIR__ . '/Source/symfony-website_skeleton-composer.json';
        $targetJsonPath = sys_get_temp_dir() . '/composer_json_manipulator_test_schema_validation.json';

        $cj = ComposerJson::fromFile($sourceJsonPath);
        $cj->save($targetJsonPath);

        $sourceJson = json_decode(file_get_contents($sourceJsonPath), true);
        $targetJson = json_decode(file_get_contents($targetJsonPath), true);

        /*
         * Check empty keys are present in "source" but not in "target"
         */
        $this->assertArrayHasKey(Section::REQUIRE_DEV, $sourceJson);
        $this->assertArrayHasKey('auto-scripts', $sourceJson['scripts']);
        $this->assertArrayNotHasKey(Section::REQUIRE_DEV, $targetJson);
        $this->assertArrayNotHasKey('auto-scripts', $targetJson['scripts']);
    }
}
