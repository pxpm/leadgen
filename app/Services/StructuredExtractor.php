<?php

declare(strict_types=1);

namespace App\Services;

class StructuredExtractor
{
    /**
     * Extract structured fields from an AI response (tool calls).
     * Handles bare JSON, markdown-fenced JSON, and common key typos.
     */
    public function extract(string $aiResponse, array $fieldDefinitions): array
    {
        $extracted = [];

        // Try to parse tool calls from the AI response (strip markdown fences first)
        $data = $this->parseToolCalls($this->stripMarkdownFences($aiResponse));

        foreach ($data as $key => $value) {
            // Correct common key typos (e.g. "rood_type" → "roof_type")
            $correctedKey = $this->correctTypo($key, $fieldDefinitions);

            if (isset($fieldDefinitions[$correctedKey])) {
                $def = $fieldDefinitions[$correctedKey];
                $isMulti = ($def['type'] ?? 'text') === 'multi_select';

                $extracted[$correctedKey] = [
                    'value' => $this->normalize($correctedKey, $value, $def),
                    'confidence' => $this->estimateConfidence($correctedKey, $value, $def),
                    'type' => $isMulti ? 'multi_select' : ($def['type'] ?? 'text'),
                ];
            }
        }

        return $extracted;
    }

    /**
     * Strip markdown code fences from the AI response so we can parse the JSON inside.
     */
    private function stripMarkdownFences(string $response): string
    {
        return preg_replace('/```(?:json)?\s*/', '', $response);
    }

    /**
     * Correct common AI key typos using Levenshtein distance.
     * No hardcoded map — any typo within edit distance 2 is auto-corrected.
     */
    private function correctTypo(string $key, array $fieldDefinitions): string
    {
        if (isset($fieldDefinitions[$key])) {
            return $key;
        }

        $best = null;
        $bestDist = PHP_INT_MAX;
        $lowerKey = mb_strtolower($key);

        foreach (array_keys($fieldDefinitions) as $defKey) {
            $dist = levenshtein($lowerKey, mb_strtolower($defKey));
            if ($dist < $bestDist && $dist <= 2) {
                $bestDist = $dist;
                $best = $defKey;
            }
        }

        return $best ?? $key;
    }

    private function parseToolCalls(string $response): array
    {
        // Try full JSON parse first (handles arrays for multi_select values)
        $decoded = json_decode($response, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Try to find a JSON block at the end of the response
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $response, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Fallback: regex for key:"value" pairs
        if (preg_match_all('/"(\w+)"\s*:\s*"([^"]+)"/', $response, $matches, PREG_SET_ORDER)) {
            $result = [];
            foreach ($matches as $m) {
                $result[$m[1]] = $m[2];
            }

            return $result;
        }

        return [];
    }

    private function normalize(string $key, mixed $value, array $definition): string
    {
        // Multi-select: join array values with comma
        if (is_array($value)) {
            return implode(', ', $value);
        }

        return (string) $value;
    }

    private function estimateConfidence(string $key, mixed $value, array $definition): float
    {
        $isMulti = ($definition['type'] ?? 'text') === 'multi_select';
        $options = $definition['options'] ?? [];

        if ($isMulti || ($definition['type'] ?? 'text') === 'select') {
            if (empty($options)) {
                return 0.8;
            }

            $values = is_array($value) ? $value : [$value];
            $matchCount = 0;
            foreach ($values as $v) {
                if (in_array((string) $v, $options, true)) {
                    $matchCount++;
                }
            }

            $ratio = count($values) > 0 ? $matchCount / count($values) : 0;

            // Scale: 0 matches → 0.5, partial → proportional, all match → 0.95
            if ($ratio === 1.0) {
                return 0.95;
            }
            if ($ratio === 0.0) {
                return 0.5;
            }

            return 0.5 + ($ratio * 0.45);
        }

        return strlen((string) $value) > 2 ? 0.9 : 0.6;
    }
}
