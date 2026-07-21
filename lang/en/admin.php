<?php

// Admin panel (Filament) translations — English
return [
    // ── Common / Shared ──
    'common' => [
        'close' => 'Close',
        'view' => 'View',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'confirm' => 'Confirm',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'select' => 'Select...',
        'configure_services' => 'Configure Services',
        'contact_support' => 'Contact Support',
        'sign_out' => 'Sign Out',
    ],

    // ── Dashboard ──
    'dashboard' => [
        'title' => 'Dashboard',
        'generate_intake_link' => 'Generate intake link',
        'link_generated' => 'Intake link generated',
        'link_expires' => 'Expires in 24 hours &middot; share with potential clients',
        'copy' => 'Copy',
        'copied' => 'Copied!',
    ],

    // ── Tenant Resource ──
    'tenant' => [
        'navigation_label' => 'Tenants',
        'model_label' => 'Tenant',
        'plural_model_label' => 'Tenants',
        'new_tenant' => 'New Tenant',
        'tenant_created' => 'Tenant Created',

        // Sections
        'section_tenant_info' => 'Tenant Information',
        'section_integrations' => 'Integrations',
        'section_advanced_config' => 'Advanced Settings (JSON)',
        'section_company_info' => 'Company Information',
        'section_admin_user' => 'Admin User',
        'section_subscription' => 'Subscription',

        // Fields
        'name' => 'Name',
        'company_name' => 'Company Name',
        'slug' => 'Slug',
        'language' => 'Language',
        'industry' => 'Industry',
        'current_plan' => 'Current Plan',
        'stripe_customer_id' => 'Stripe Customer ID',
        'twilio_number' => 'Twilio Number',
        'twilio_phone_sid' => 'Twilio Phone SID',
        'branding_config' => 'Branding Config',
        'notification_config' => 'Notification Config',
        'qualification_overrides' => 'Qualification Overrides',
        'plan' => 'Plan',
        'status' => 'Status',
        'trial_end' => 'Trial End',
        'send_magic_link' => 'Send magic link',
        'send_magic_link_help' => 'The admin will receive an email with a link to log in and set their password.',
        'created_at' => 'Created',

        // Subscription statuses
        'status_active' => 'Active',
        'status_trial' => 'Trial',
        'status_past_due' => 'Past Due',
        'status_canceled' => 'Canceled',

        // Language options
        'lang_pt' => 'Portuguese',
        'lang_en' => 'English',

        // Table columns
        'column_hash' => '#',
        'column_plan' => 'Plan',
        'column_created' => 'Created',
    ],

    // ── Lead Resource ──
    'lead' => [
        'navigation_label' => 'Leads',
        'model_label' => 'Lead',
        'plural_model_label' => 'Leads',
        'delivered_action' => 'Delivered',
        'not_informed' => 'Not provided',

        // Sections
        'section_contact' => 'Contact',
        'section_details' => 'Details',
        'section_status' => 'Status',
        'section_notes' => 'Notes',

        // Status labels
        'status_new' => 'New',
        'status_in_progress' => 'In Progress',
        'status_qualified' => 'Qualified',
        'status_delivered' => 'Delivered',
        'status_unknown' => 'Unknown',

        // Fields
        'contact_name' => 'Name',
        'phone' => 'Phone',
        'email' => 'Email',
        'property_address' => 'Address',
        'postal_code' => 'Postal Code',
        'property_type' => 'Property Type',
        'problem_type' => 'Problem Type',
        'roof_type' => 'Roof Type',
        'building_type' => 'Building Type',
        'urgency' => 'Urgency',
        'insurance_claim' => 'Insurance Claim',
        'roof_size' => 'Roof Size',
        'roof_age' => 'Roof Age',
        'leak_location' => 'Leak Location',
        'source' => 'Source',
        'score' => 'Score',
        'notes' => 'Notes',
        'additional_notes' => 'Additional Notes',
        'status' => 'Status',

        // Table columns
        'column_hash' => '#',
        'column_service' => 'Service',
        'column_status' => 'Status',
        'column_source' => 'Source',
        'column_score' => 'Score',
        'column_date' => 'Date',

        // Service type labels
        'service_roofing' => '🏠 Roofing',
        'service_waterproofing' => '💧 Waterproofing',
        'service_painting' => '🎨 Painting',
        'service_insulation' => '🔧 Insulation',
        'service_facades' => '🏢 Facades',
        'service_terraces' => '🌿 Terraces',
        'service_gutters' => '🏚️ Gutters',
        'service_remodeling' => '🔨 Remodeling',
    ],

    // ── Lead View Page Actions ──
    'lead_actions' => [
        'decline' => 'Decline Lead',
        'decline_heading' => 'Decline Lead',
        'decline_description' => 'Select reason(s) to decline this lead and generate a professional email.',

        'request_info' => 'Request Information',
        'request_info_heading' => 'Request Information from Client',
        'request_info_description' => 'Select the information you need and generate a professional email.',

        'quote_followup' => 'Quote Follow-up',
        'quote_followup_heading' => 'Quote Follow-up',
        'quote_followup_description' => 'Select the follow-up stage and generate a professional email.',

        'general_contact' => 'General Contact',
        'general_contact_heading' => 'General Contact',
        'general_contact_description' => 'Write notes and generate a custom email.',
    ],

    // ── Email Accounts Relation Manager ──
    'email_accounts' => [
        'title' => 'Email Accounts',
        'provider' => 'Provider',
        'provider_google' => 'Google (Gmail)',
        'provider_microsoft' => 'Microsoft (Outlook)',
        'provider_custom' => 'Other / Custom',
        'provider_google_short' => '🔵 Google',
        'provider_microsoft_short' => '🔷 Microsoft',
        'provider_custom_short' => '⚙️ Custom',
        'email' => 'Email',
        'display_name' => 'Display Name',
        'app_password' => 'App Password',
        'app_password_help' => 'App password generated in Google/Microsoft account settings.',
        'watch_folder' => 'Folder for new leads',
        'watch_folder_help' => 'IMAP folder name (e.g. Leads, [Gmail]/Quotes)...',
        'auto_create_leads' => 'Auto-create leads',
        'auto_create_leads_help' => 'When enabled, emails from unknown senders in this folder automatically create leads.',
        'status' => 'Status',
        'last_sync' => 'Last Sync',
        'never' => 'Never',
    ],

    // ── Email Messages Relation Manager ──
    'email_messages' => [
        'title' => 'Emails',
        'from_to' => 'From / To',
        'subject' => 'Subject',
        'preview' => 'Preview',
        'date' => 'Date',
        'no_subject' => '(no subject)',
        'detail_from' => 'From:',
        'detail_to' => 'To:',
        'detail_cc' => 'CC:',
        'detail_date' => 'Date:',
        'detail_attachments' => 'Attachments:',
    ],
];
