{{ __('emails.lead_qualified.heading') }}
====================

{{ __('emails.lead_qualified.label_name') }} {{ $fields['contact_name'] ?? 'N/A' }}
{{ __('emails.lead_qualified.label_phone') }} {{ $fields['phone'] ?? 'N/A' }}
{{ __('emails.lead_qualified.label_email') }} {{ $fields['email'] ?? 'N/A' }}
{{ __('emails.lead_qualified.label_address') }} {{ $fields['property_address'] ?? 'N/A' }}

{{ __('emails.lead_qualified.label_problem') }} {{ $fields['problem_type'] ?? 'N/A' }}
{{ __('emails.lead_qualified.label_roof') }} {{ $fields['roof_type'] ?? 'N/A' }}
{{ __('emails.lead_qualified.label_urgency') }} {{ $fields['urgency'] ?? 'N/A' }}

{{ __('emails.lead_qualified.label_score') }} {{ $score ?? 'N/A' }}/10

@if(!empty($photos))
{{ __('emails.lead_qualified.label_photos') }}
@foreach($photos as $url)
- {{ $url }}
@endforeach
@endif

{{ __('emails.lead_qualified.view_lead') }} {{ route('filament.admin.resources.leads.view', $lead) }}
