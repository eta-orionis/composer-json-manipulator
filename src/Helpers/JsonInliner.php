<?php

declare(strict_types=1);

namespace EtaOrionis\ComposerJsonManipulator\Helpers;

final class JsonInliner
{
    /**
     * @var string
     * @see https://regex101.com/r/jhWo9g/1
     */
    private const SPACE_REGEX = '#\s+#';

    public function inlineSections(string $jsonContent, $inlineSections=['keywords']): string
    {
        foreach ($inlineSections as $inlineSection) {
            $pattern = '#("' . preg_quote($inlineSection, '#') . '": )\[(.*?)\](,)#ms';

            $jsonContent = preg_replace_callback($pattern, function (array $match): string {
                $inlined = preg_replace(self::SPACE_REGEX, ' ', $match[2]);
                $inlined = trim($inlined);
                $inlined = '[' . $inlined . ']';
                return $match[1] . $inlined . $match[3];
            }, $jsonContent);
        }

        return $jsonContent;
    }
}
