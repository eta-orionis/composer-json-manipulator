<?php

declare(strict_types=1);

namespace EtaOrionis\ComposerJsonManipulator\Helpers;

final class JsonCleaner
{
    /**
     * @param array<int|string, mixed> $data
     * @return array<int|string, mixed>
     */
    public function removeEmptyKeysFromJsonArray(array $data): array
    {
        foreach ($data as $key => $value) {
	    if ($value === null) {
	    	unset($data[$key]);
	    }
	    
	    if (! is_array($value)) {
                continue;
            }

            if ($value === []) {
                unset($data[$key]);
            } else {
                $data[$key] = $this->removeEmptyKeysFromJsonArray($value);
                //could have been emptied as result of recursive call, check again
                if ($data[$key] === []) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }
}
