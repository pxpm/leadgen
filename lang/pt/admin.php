<?php

// Admin panel (Filament) translations — Portuguese
return [
    // ── Common / Shared ──
    'common' => [
        'close' => 'Fechar',
        'view' => 'Ver',
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
        'confirm' => 'Confirmar',
        'create' => 'Criar',
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'select' => 'Selecionar...',
        'configure_services' => 'Configurar Serviços',
        'contact_support' => 'Contactar Suporte',
        'sign_out' => 'Sair',
    ],

    // ── Dashboard ──
    'dashboard' => [
        'title' => 'Dashboard',
        'generate_intake_link' => 'Gerar link de qualificação',
        'link_generated' => 'Link de qualificação gerado',
        'link_expires' => 'Expira em 24 horas &middot; partilhe com potenciais clientes',
        'copy' => 'Copiar',
        'copied' => 'Copiado!',
    ],

    // ── Tenant Resource ──
    'tenant' => [
        'navigation_label' => 'Tenants',
        'model_label' => 'Tenant',
        'plural_model_label' => 'Tenants',
        'new_tenant' => 'Novo Tenant',
        'tenant_created' => 'Tenant Criado',

        // Sections
        'section_tenant_info' => 'Informação do Tenant',
        'section_integrations' => 'Integrações',
        'section_advanced_config' => 'Configurações Avançadas (JSON)',
        'section_company_info' => 'Informação da Empresa',
        'section_admin_user' => 'Utilizador Admin',
        'section_subscription' => 'Subscrição',

        // Fields
        'name' => 'Nome',
        'company_name' => 'Nome da Empresa',
        'slug' => 'Slug',
        'language' => 'Idioma',
        'industry' => 'Indústria',
        'current_plan' => 'Plano Atual',
        'stripe_customer_id' => 'Stripe Customer ID',
        'twilio_number' => 'Número Twilio',
        'twilio_phone_sid' => 'Twilio Phone SID',
        'branding_config' => 'Branding Config',
        'notification_config' => 'Notification Config',
        'qualification_overrides' => 'Qualification Overrides',
        'plan' => 'Plano',
        'status' => 'Estado',
        'trial_end' => 'Fim do Trial',
        'send_magic_link' => 'Enviar magic link',
        'send_magic_link_help' => 'O admin receberá um email com um link para fazer login e definir a password.',
        'created_at' => 'Criado',

        // Subscription statuses
        'status_active' => 'Ativo',
        'status_trial' => 'Trial',
        'status_past_due' => 'Pagamento em Atraso',
        'status_canceled' => 'Cancelado',

        // Language options
        'lang_pt' => 'Português',
        'lang_en' => 'English',

        // Table columns
        'column_hash' => '#',
        'column_plan' => 'Plano',
        'column_created' => 'Criado',
    ],

    // ── Lead Resource ──
    'lead' => [
        'navigation_label' => 'Leads',
        'model_label' => 'Lead',
        'plural_model_label' => 'Leads',
        'delivered_action' => 'Entregue',
        'not_informed' => 'Não informado',

        // Sections
        'section_contact' => 'Contacto',
        'section_details' => 'Detalhes',
        'section_status' => 'Estado',
        'section_notes' => 'Notas',

        // Status labels
        'status_new' => 'Novo',
        'status_in_progress' => 'Em progresso',
        'status_qualified' => 'Qualificado',
        'status_delivered' => 'Entregue',
        'status_unknown' => 'Desconhecido',

        // Fields
        'contact_name' => 'Nome',
        'phone' => 'Telefone',
        'email' => 'Email',
        'property_address' => 'Morada',
        'postal_code' => 'Código Postal',
        'property_type' => 'Tipo de Propriedade',
        'problem_type' => 'Tipo de Problema',
        'roof_type' => 'Tipo de Telhado',
        'building_type' => 'Tipo de Moradia',
        'urgency' => 'Urgência',
        'insurance_claim' => 'Sinistro de Seguro',
        'roof_size' => 'Tamanho do Telhado',
        'roof_age' => 'Idade do Telhado',
        'leak_location' => 'Local da Infiltração',
        'source' => 'Origem',
        'score' => 'Pontuação',
        'notes' => 'Notas',
        'additional_notes' => 'Notas adicionais',
        'status' => 'Estado',

        // Table columns
        'column_hash' => '#',
        'column_service' => 'Serviço',
        'column_status' => 'Estado',
        'column_source' => 'Origem',
        'column_score' => 'Score',
        'column_date' => 'Data',

        // Service type labels
        'service_roofing' => '🏠 Telhados',
        'service_waterproofing' => '💧 Impermeabilização',
        'service_painting' => '🎨 Pintura',
        'service_insulation' => '🔧 Isolamento',
        'service_facades' => '🏢 Fachadas',
        'service_terraces' => '🌿 Terraços',
        'service_gutters' => '🏚️ Algerozes',
        'service_remodeling' => '🔨 Remodelação',
    ],

    // ── Lead View Page Actions ──
    'lead_actions' => [
        'email_followup' => 'Email',

        'decline' => 'Rejeitar Lead',
        'decline_heading' => 'Rejeitar Lead',
        'decline_description' => 'Selecione o(s) motivo(s) para rejeitar este lead e gere um email profissional.',

        'request_info' => 'Pedir Informações',
        'request_info_heading' => 'Pedir Informações ao Cliente',
        'request_info_description' => 'Selecione a informação que precisa e gere um email profissional.',

        'quote_followup' => 'Acompanhar Orçamento',
        'quote_followup_heading' => 'Acompanhar Orçamento',
        'quote_followup_description' => 'Selecione o estágio do acompanhamento e gere um email profissional.',

        'general_contact' => 'Contacto Geral',
        'general_contact_heading' => 'Contacto Geral',
        'general_contact_description' => 'Escreva notas e gere um email personalizado.',
    ],

    // ── Email Accounts Relation Manager ──
    'email_accounts' => [
        'title' => 'Contas de Email',
        'provider' => 'Fornecedor',
        'provider_google' => 'Google (Gmail)',
        'provider_microsoft' => 'Microsoft (Outlook)',
        'provider_custom' => 'Outro / Customizado',
        'provider_google_short' => '🔵 Google',
        'provider_microsoft_short' => '🔷 Microsoft',
        'provider_custom_short' => '⚙️ Custom',
        'email' => 'Email',
        'display_name' => 'Nome de exibição',
        'app_password' => 'App Password',
        'app_password_help' => 'Palavra-passe de aplicação gerada nas definições da conta Google/Microsoft.',
        'watch_folder' => 'Pasta para novos leads',
        'watch_folder_help' => 'IMAP folder name (ex: Leads, [Gmail]/Orçamentos)...',
        'auto_create_leads' => 'Criar leads automaticamente',
        'auto_create_leads_help' => 'Quando ativo, emails de remetentes desconhecidos nesta pasta criam leads automaticamente.',
        'status' => 'Estado',
        'last_sync' => 'Última sincronização',
        'never' => 'Nunca',
    ],

    // ── Email Messages Relation Manager ──
    'email_messages' => [
        'title' => 'Emails',
        'from_to' => 'De / Para',
        'subject' => 'Assunto',
        'preview' => 'Pré-visualização',
        'date' => 'Data',
        'no_subject' => '(sem assunto)',
        'detail_from' => 'De:',
        'detail_to' => 'Para:',
        'detail_cc' => 'CC:',
        'detail_date' => 'Data:',
        'detail_attachments' => 'Anexos:',
    ],

    // ── Subscriptions Relation Manager ──
    'subscriptions' => [
        'title' => 'Subscrições',
        'plan' => 'Plano',
        'plan_change_help' => 'Alterar o plano faz upgrade ou downgrade da subscrição.',
        'change_plan' => 'Alterar Plano',
        'column_hash' => '#',
        'column_status' => 'Estado',
        'column_trial_end' => 'Fim do Trial',
        'column_ends_at' => 'Termina em',
        'column_created' => 'Criado',
    ],

    // ── Plans Resource ──
    'plans' => [
        'navigation_label' => 'Planos',
        'model_label' => 'Plano',
        'plural_model_label' => 'Planos',
        'section_info' => 'Informação do Plano',
        'section_monthly_limits' => 'Limites Mensais',
        'section_visibility' => 'Visibilidade',
        'name' => 'Nome',
        'slug' => 'Slug',
        'description' => 'Descrição',
        'sms' => 'SMS',
        'emails' => 'Emails',
        'ai_ingestion' => 'AI Ingestion',
        'public_plan' => 'Plano Público',
        'public_plan_help' => 'Visível na página de preços para self-serve.',
        'active' => 'Ativo',
        'active_help' => 'Planos inativos não podem ser atribuídos.',
        'order' => 'Ordem',
        'column_hash' => '#',
        'column_sms_month' => 'SMS/mês',
        'column_emails_month' => 'Emails/mês',
        'column_ai_month' => 'AI/mês',
        'column_public' => 'Público',
        'column_active' => 'Ativo',
        'column_order' => 'Ordem',
        'column_created' => 'Criado',
    ],

    // ── Service Configuration ──
    'service_config' => [
        'title' => 'Configuração de Serviços',
        'breadcrumb' => 'Serviços',
        'saved' => 'Configuração guardada!',
        'tab_services' => 'Serviços',
        'tab_email' => 'Email',

        'section_base_fields' => 'Campos Base',
        'section_base_fields_desc' => 'Ative/desative campos e defina se são obrigatórios ou opcionais.',

        'section_conditional_fields' => 'Campos Condicionais',
        'section_conditional_fields_desc' => 'Campos que só aparecem quando certas condições se verificam.',

        'section_conditional_rules' => 'Regras Condicionais',
        'section_conditional_rules_desc' => 'Quando certas condições se verificam, exigir campos extra.',

        'section_messages' => 'Mensagens',
        'section_messages_desc' => 'Personalizar textos para este tenant.',

        'add_conditional_rule' => 'Adicionar regra condicional',
        'conditions_fieldset' => 'Condições (todas têm de ser verdade)',
        'add_condition' => 'Adicionar condição',
        'field' => 'Campo',
        'values' => 'Valor(es)',
        'require_these_fields' => 'Exigir também estes campos',

        'greeting_message' => 'Mensagem de saudação',
        'customize_field_questions' => 'Personalizar perguntas por campo',
        'add_custom_question' => 'Personalizar pergunta',
        'question_text' => 'Texto da pergunta',
    ],

    // ── Manual Lead Intake ──
    'manual_lead_intake' => [
        'title' => 'Criar Lead',
        'navigation_label' => 'Criar Lead',
        'modal_description' => 'Cola o email do cliente. O serviço é detetado automaticamente.',
        'lead_created' => 'Lead criado! Link enviado para o cliente.',
        'new_lead' => 'Novo Lead',

        'section_email' => 'Email do Cliente',
        'service_type' => 'Tipo de Serviço',
        'email_content' => 'Conteúdo do Email',
        'email_placeholder' => 'Cola aqui o texto do email do cliente...',
        'extract_data' => 'Extrair Dados',

        'section_extracted' => 'Campos Extraídos',
        'section_missing' => 'Campos em Falta',
        'missing_description' => 'Estes campos são obrigatórios e ainda não foram preenchidos',

        'create_lead_send_link' => 'Criar Lead e Enviar Link',
        'send_link_description' => 'O cliente receberá um link para continuar o processo e fornecer os campos em falta.',
    ],

    // ── Subscription Inactive ──
    'subscription_inactive' => [
        'title' => 'Subscrição Inativa',
        'message' => 'A sua subscrição não está ativa. Contacte o suporte para reativar a sua conta ou atualizar o seu plano.',
    ],

    // ── Navigation ──
    'navigation' => [
        'configuration' => 'Configuração',
    ],

    // ── Dashboard Widgets ──
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
];
