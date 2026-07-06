<?php

namespace TomShaw\Dropbox\Support;

final class Arr
{
    /**
     * Normalize an untyped payload (decoded JSON, session data) into a
     * string-keyed array, or null when the value is not an array.
     *
     * @return array<string, mixed>|null
     */
    public static function stringKeyed(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $normalized = [];

        foreach ($value as $key => $item) {
            $normalized[(string) $key] = $item;
        }

        return $normalized;
    }
}
