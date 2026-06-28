<?php

namespace App\Support\Assessment;

class ChoiceOptionNormalizer
{
    public static function normalizeMany(?array $options): array
    {
        $normalizedOptions = [];

        foreach ($options ?? [] as $index => $option) {
            $normalizedOption = static::normalize($option, $index);

            if ($normalizedOption['label'] === '' && $normalizedOption['value'] === '') {
                continue;
            }

            $normalizedOptions[] = $normalizedOption;
        }

        return array_values($normalizedOptions);
    }

    public static function normalize(mixed $option, ?int $index = null): array
    {
        if (! is_array($option)) {
            $text = trim((string) $option);

            return [
                'label' => $text,
                'value' => $text,
                'aliases' => $text !== '' ? [$text] : [],
            ];
        }

        $rawLabel = trim((string) ($option['label'] ?? ''));
        $rawValue = trim((string) ($option['value'] ?? ''));

        if (static::shouldSwapLabelAndValue($rawLabel, $rawValue)) {
            [$rawLabel, $rawValue] = [$rawValue, $rawLabel];
        }

        $label = $rawLabel;
        $value = $rawValue;

        if ($label === '' && $value !== '') {
            $label = $value;
        }

        if ($value === '' && $label !== '') {
            $value = $label;
        }

        $aliases = array_values(array_unique(array_filter([
            $value,
            $label,
            trim((string) ($option['value'] ?? '')),
            trim((string) ($option['label'] ?? '')),
        ], fn ($item) => $item !== '')));

        return [
            'label' => $label,
            'value' => $value,
            'aliases' => $aliases,
        ];
    }

    private static function shouldSwapLabelAndValue(string $label, string $value): bool
    {
        if ($label === '' || $value === '') {
            return false;
        }

        return static::looksLikeCode($label) && static::looksLikeAnswerText($value);
    }

    private static function looksLikeCode(string $value): bool
    {
        if ($value === '' || preg_match('/\s/', $value) === 1) {
            return false;
        }

        if (mb_strlen($value) > 6) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9._-]+$/', $value) === 1;
    }

    private static function looksLikeAnswerText(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        return preg_match('/\s/', $value) === 1 || mb_strlen($value) >= 6;
    }
}
