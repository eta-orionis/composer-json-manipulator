<?php

declare(strict_types=1);

namespace EtaOrionis\ComposerJsonManipulator\Tests\PackageSorter;

use Iterator;
use EtaOrionis\ComposerJsonManipulator\Helpers\PackageSorter;
use PHPUnit\Framework\TestCase;

final class PackageSorterTest extends TestCase
{
    private PackageSorter $packageSorter;

    protected function setUp(): void
    {
        $this->packageSorter = new PackageSorter();
    }

    /**
     * @dataProvider provideData()
     * @param array<string, string> $packages
     * @param array<string, string> $expectedSortedPackages
     */
    public function test(array $packages, array $expectedSortedPackages): void
    {
        $sortedPackages = $this->packageSorter->sortPackages($packages);
        $this->assertSame($expectedSortedPackages, $sortedPackages);
    }

    /**
     * @return Iterator<array<int, array<string, string>>>
     */
    public function provideData(): Iterator
    {
        yield [
            [
                'symfony/console' => '^5.2',
                'php' => '^8.0',
                'ext-json' => '*',
            ],
            [
                'php' => '^8.0',
                'ext-json' => '*',
                'symfony/console' => '^5.2',
            ],
        ];
    }
}
