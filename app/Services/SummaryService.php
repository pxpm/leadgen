<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;

class SummaryService
{
    /**
     * Generate an AI-powered summary of a qualified lead.
     * For MVP, generates a structured summary from collected fields.
     */
    public function generate(Lead $lead): string
    {
        $fields = $lead->fields->pluck('field_value', 'field_key')->toArray();
        $locale = $lead->tenant->locale ?? 'pt';

        // TODO: Use AI (DeepSeek) for richer summaries
        // For now, generate a template-based summary
        $lines = [];
        $lines[] = $locale === 'pt' ? 'Resumo do Lead' : 'Lead Summary';
        $lines[] = '';

        if ($name = $fields['contact_name'] ?? null) {
            $lines[] = ($locale === 'pt' ? 'Cliente: ' : 'Customer: ').$name;
        }
        if ($phone = $fields['phone'] ?? null) {
            $lines[] = ($locale === 'pt' ? 'Telefone: ' : 'Phone: ').$phone;
        }
        if ($email = $fields['email'] ?? null) {
            $lines[] = 'Email: '.$email;
        }
        if ($address = $fields['property_address'] ?? null) {
            $lines[] = ($locale === 'pt' ? 'Morada: ' : 'Address: ').$address;
        }
        $lines[] = '';

        if ($problem = $fields['problem_type'] ?? null) {
            $lines[] = ($locale === 'pt' ? 'Tipo de Problema: ' : 'Issue: ').$problem;
        }
        // Render all remaining fields generically (label via field key)
        $skipKeys = ['contact_name', 'phone', 'email', 'property_address', 'problem_type', 'roof_type', 'urgency'];
        $fieldLabels = [
            'pt' => [
                'roof_type' => 'Tipo de Cobertura',
                'problem_type' => 'Tipo de Intervenção',
                'urgency' => 'Urgência',
            ],
            'en' => [
                'roof_type' => 'Coverage Type',
                'problem_type' => 'Issue Type',
                'urgency' => 'Urgency',
            ],
        ];

        foreach ($fields as $key => $value) {
            if (in_array($key, $skipKeys, true)) {
                continue;
            }
            if (is_string($value) && $value !== '') {
                $label = $fieldLabels[$locale][$key] ?? ucfirst(str_replace('_', ' ', $key));
                $lines[] = $label.': '.$value;
            }
        }
        if ($urgency = $fields['urgency'] ?? null) {
            $lines[] = ($locale === 'pt' ? 'Urgência: ' : 'Urgency: ').$urgency;
        }

        $hasPhotos = $lead->getMedia('photos')->isNotEmpty();
        $lines[] = '';
        $lines[] = $hasPhotos
            ? ($locale === 'pt' ? 'Fotos: Sim' : 'Photos: Yes')
            : ($locale === 'pt' ? 'Fotos: Não' : 'Photos: No');

        return implode("\n", $lines);
    }
}
