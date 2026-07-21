<?php

// Widget and SMS/missed call translations — English
return [
    'stats' => [
        'total_leads' => 'Total Leads',
        'total_leads_desc' => 'All leads',
        'qualified_leads' => 'Qualified Leads',
        'qualified_leads_desc' => ':rate% conversion rate',
        'missed_calls' => 'Missed Calls',
        'missed_calls_desc' => 'Total calls',
        'recovered_calls' => 'Recovered Calls',
        'recovered_calls_desc' => ':rate% recovered',
        'recovered_calls_desc_zero' => '0%',
    ],

    'leads_table' => [
        'heading' => 'Leads',
        'description' => 'All received leads',
        'column_hash' => '#',
        'column_service' => 'Service',
        'column_name' => 'Name',
        'column_phone' => 'Phone',
        'column_email' => 'Email',
        'column_status' => 'Status',
        'column_source' => 'Source',
        'column_date' => 'Date',
        'action_view' => 'View',
    ],

    'missed_calls_table' => [
        'heading' => 'Missed Calls',
        'description' => 'All missed calls',
        'column_number' => 'Number',
        'column_line' => 'Line',
        'column_match' => 'Match',
        'column_match_dedicated' => 'Direct number',
        'column_match_forwarded' => 'Forwarded',
        'column_intent' => 'Intent',
        'column_sms' => 'SMS?',
        'column_lead' => 'Lead',
        'column_date' => 'Date',
    ],

    'missed_call_sms' => [
        'body' => 'Missed a call from :number. Click to send follow-up SMS: :url',
        'default_template' => 'Sorry we missed your call. Tap here to leave us a message: :url',
    ],

    'sms_sent_page' => [
        'title' => ':name - SMS Sent',
        'heading' => 'SMS sent!',
        'description' => 'The customer will receive a link to leave a message. You\'ll be notified when they respond.',
    ],

    'missed_call_landing' => [
        'title' => ':name - Assistant',
    ],

    'widget_test' => [
        'title' => 'LeadGen — Widget Test',
        'company_name' => '🏠 Telhados Lisboa',
        'phone' => '📞 210 000 001',
        'headline' => 'Need help with your roof?',
        'subheadline' => 'Answer a few questions and get a quick estimate.',
        'cta' => 'Request Free Estimate',
        'feature_1_title' => '🕐 Instant Response',
        'feature_1_desc' => 'No need to wait for a callback.',
        'feature_2_title' => '📸 Send Photos',
        'feature_2_desc' => 'Take photos of the issue directly from your phone.',
        'feature_3_title' => '📋 Quick Quote',
        'feature_3_desc' => 'Get a quote based on the information you share.',
    ],

    'widget_api' => [
        'default_greeting' => 'Hello! How can we help?',
        'session_expired' => 'This session has expired. Please start a new conversation.',
        'missed_call_welcome' => 'Hello! How can we help?',
        'intent_quote' => 'I want a quote',
        'intent_report' => 'Report an issue',
        'intent_other' => 'Other',
        'invalid_collection' => 'File type not accepted for this collection.',
        'invalid_file_type' => 'Unsupported format. Accepted formats: :types.',
    ],

    'intake_errors' => [
        'link_expired' => 'This link has expired.',
        'unknown_link_type' => 'Unknown link type.',
    ],

    'console' => [
        'email_poll_no_accounts' => 'No active email accounts to poll.',
        'email_poll_dispatched' => 'Dispatched poll jobs for :count account(s).',
    ],
];
