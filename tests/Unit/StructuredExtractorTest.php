<?php

use App\Services\StructuredExtractor;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->extractor = new StructuredExtractor;
});

test('extracts fields from JSON tool calls in AI response', function () {
    $aiResponse = 'Obrigado pela informação. {"problem_type":"repair","roof_type":"tile"}';

    $definitions = [
        'problem_type' => ['type' => 'select', 'options' => ['repair', 'replacement']],
        'roof_type' => ['type' => 'select', 'options' => ['tile', 'slate', 'metal']],
    ];

    $result = $this->extractor->extract($aiResponse, $definitions);

    expect($result)->toHaveKeys(['problem_type', 'roof_type']);
    expect($result['problem_type']['value'])->toBe('repair');
    expect($result['problem_type']['confidence'])->toBe(0.95);
    expect($result['roof_type']['value'])->toBe('tile');
});

test('returns empty array when no JSON found', function () {
    $result = $this->extractor->extract('Apenas texto sem JSON.', []);

    expect($result)->toBe([]);
});

test('lower confidence for unknown select values', function () {
    $aiResponse = '{"roof_type":"diamond"}';

    $definitions = ['roof_type' => ['type' => 'select', 'options' => ['tile', 'slate']]];

    $result = $this->extractor->extract($aiResponse, $definitions);

    expect($result['roof_type']['confidence'])->toBe(0.5);
});

test('corrects common AI key typos', function () {
    $aiResponse = '{"rood_type":"tile","problem_type":"repair"}';

    $definitions = [
        'roof_type' => ['type' => 'select', 'options' => ['tile', 'slate']],
        'problem_type' => ['type' => 'select', 'options' => ['repair', 'replacement']],
    ];

    $result = $this->extractor->extract($aiResponse, $definitions);

    expect($result)->toHaveKeys(['roof_type', 'problem_type']);
    expect($result['roof_type']['value'])->toBe('tile');
});

test('extracts JSON from markdown code fences', function () {
    $aiResponse = "Obrigado!\n\n```json\n{\"paint_scope\":\"interior\"}\n```";

    $definitions = ['paint_scope' => ['type' => 'select', 'options' => ['interior', 'exterior']]];

    $result = $this->extractor->extract($aiResponse, $definitions);

    expect($result)->toHaveKey('paint_scope');
    expect($result['paint_scope']['value'])->toBe('interior');
});

test('fuzzy matches keys within edit distance 2', function () {
    $aiResponse = '{"roof_typ":"tile"}';  // missing 'e'

    $definitions = ['roof_type' => ['type' => 'select', 'options' => ['tile', 'slate']]];

    $result = $this->extractor->extract($aiResponse, $definitions);

    expect($result)->toHaveKey('roof_type');
    expect($result['roof_type']['value'])->toBe('tile');
});
