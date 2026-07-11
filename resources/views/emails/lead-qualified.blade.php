Novo Lead Qualificado
====================

Nome: {{ $fields['contact_name'] ?? 'N/A' }}
Telefone: {{ $fields['phone'] ?? 'N/A' }}
Email: {{ $fields['email'] ?? 'N/A' }}
Morada: {{ $fields['property_address'] ?? 'N/A' }}

Problema: {{ $fields['problem_type'] ?? 'N/A' }}
Telhado: {{ $fields['roof_type'] ?? 'N/A' }}
Urgência: {{ $fields['urgency'] ?? 'N/A' }}

Pontuação: {{ $score ?? 'N/A' }}/10

@if(!empty($photos))
Fotos:
@foreach($photos as $url)
- {{ $url }}
@endforeach
@endif

Ver lead: {{ route('filament.admin.resources.leads.view', $lead) }}
