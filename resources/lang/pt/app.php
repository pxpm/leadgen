<?php

// Widget and SMS/missed call translations — Portuguese
return [
    'stats' => [
        'total_leads' => 'Total de Leads',
        'total_leads_desc' => 'Todos os leads',
        'qualified_leads' => 'Leads Qualificados',
        'qualified_leads_desc' => ':rate% de conversão',
        'missed_calls' => 'Chamadas Perdidas',
        'missed_calls_desc' => 'Total de chamadas',
        'recovered_calls' => 'Chamadas Recuperadas',
        'recovered_calls_desc' => ':rate% recuperação',
        'recovered_calls_desc_zero' => '0%',
    ],

    'leads_table' => [
        'heading' => 'Leads',
        'description' => 'Todos os leads recebidos',
        'column_hash' => '#',
        'column_service' => 'Serviço',
        'column_name' => 'Nome',
        'column_phone' => 'Telefone',
        'column_email' => 'Email',
        'column_status' => 'Estado',
        'column_source' => 'Origem',
        'column_date' => 'Data',
        'action_view' => 'Ver',
    ],

    'missed_calls_table' => [
        'heading' => 'Chamadas Perdidas',
        'description' => 'Todas as chamadas não atendidas',
        'column_number' => 'Número',
        'column_line' => 'Linha',
        'column_match' => 'Correspondência',
        'column_match_dedicated' => 'Número direto',
        'column_match_forwarded' => 'Reencaminhado',
        'column_intent' => 'Intenção',
        'column_sms' => 'SMS?',
        'column_lead' => 'Lead',
        'column_date' => 'Data',
    ],

    'missed_call_sms' => [
        'body' => 'Perdeu uma chamada de :number. Clique para enviar SMS de follow-up: :url',
        'default_template' => 'Desculpe, não podemos atender agora. Toque aqui para nos deixar uma mensagem: :url',
    ],

    'sms_sent_page' => [
        'title' => ':name - SMS Enviado',
        'heading' => 'SMS enviado!',
        'description' => 'O cliente receberá um link para deixar uma mensagem. Quando o cliente responder, será notificado.',
    ],

    'missed_call_landing' => [
        'title' => ':name - Assistente',
    ],

    'widget_test' => [
        'title' => 'LeadGen — Teste do Widget',
        'company_name' => '🏠 Telhados Lisboa',
        'phone' => '📞 210 000 001',
        'headline' => 'Precisa de ajuda com o seu telhado?',
        'subheadline' => 'Responda a algumas perguntas e receba um orçamento rapidamente.',
        'cta' => 'Pedir Orçamento Grátis',
        'feature_1_title' => '🕐 Resposta Imediata',
        'feature_1_desc' => 'Não precisa esperar por uma chamada de volta.',
        'feature_2_title' => '📸 Envie Fotos',
        'feature_2_desc' => 'Tire fotos do problema diretamente pelo telemóvel.',
        'feature_3_title' => '📋 Orçamento Rápido',
        'feature_3_desc' => 'Receba um orçamento baseado nas informações que partilhar.',
    ],

    'widget_api' => [
        'default_greeting' => 'Olá! Em que podemos ajudar?',
        'session_expired' => 'Esta sessão expirou. Por favor inicie uma nova conversa.',
        'missed_call_welcome' => 'Olá! Como podemos ajudar?',
        'intent_quote' => 'Quero um orçamento',
        'intent_report' => 'Reportar um problema',
        'intent_other' => 'Outro assunto',
        'invalid_collection' => 'Tipo de ficheiro não aceite para esta coleção.',
        'invalid_file_type' => 'Formato não suportado. Formatos aceites: :types.',
    ],

    'intake_errors' => [
        'link_expired' => 'Este link expirou.',
        'unknown_link_type' => 'Tipo de link desconhecido.',
    ],

    'console' => [
        'email_poll_no_accounts' => 'No active email accounts to poll.',
        'email_poll_dispatched' => 'Dispatched poll jobs for :count account(s).',
    ],
];
