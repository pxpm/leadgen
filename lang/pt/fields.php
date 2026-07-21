<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Field Labels & Descriptions
    |--------------------------------------------------------------------------
    |
    | Human-readable labels and descriptions for all qualification fields.
    | Used in the admin panel for service configuration.
    | Keys match field keys in industry configs.
    |
    */
    'labels' => [
        // ── Shared / Base ──
        'contact_name' => 'Nome',
        'phone' => 'Telefone',
        'email' => 'Email',
        'property_address' => 'Morada',
        'postal_code' => 'Código Postal',
        'notes' => 'Notas',

        // ── Roofing ──
        'roof_type' => 'Tipo de Telhado',
        'problem_type' => 'Tipo de Problema',
        'urgency' => 'Urgência',
        'insurance_claim' => 'Sinistro de Seguro',
        'roof_age' => 'Idade do Telhado',
        'roof_size' => 'Tamanho do Telhado',
        'leak_location' => 'Local da Infiltração',
        'asbestos_removal_required' => 'Remoção de Amianto',

        // ── Painting ──
        'paint_scope' => 'Âmbito da Pintura',
        'surface_condition' => 'Estado das Superfícies',
        'building_type' => 'Tipo de Edifício',

        // ── Remodeling ──
        'remodel_scope' => 'Âmbito da Remodelação',
        'timeline' => 'Prazo',
        'budget_range' => 'Orçamento',

        // ── Terraces ──
        'terrace_type' => 'Tipo de Terraço',
        'current_surface' => 'Revestimento Atual',

        // ── Insulation ──
        'insulation_type' => 'Tipo de Isolamento',
        'current_insulation' => 'Isolamento Atual',

        // ── Waterproofing ──
        'work_type' => 'Tipo de Trabalho',
        'surface_type' => 'Tipo de Superfície',

        // ── Facades ──
        'facade_material' => 'Material da Fachada',
        'building_height' => 'Altura do Edifício',
        'access_type' => 'Tipo de Acesso',
        'access_method' => 'Método de Acesso',

        // ── Gutters ──
        'gutter_material' => 'Material da Caleira',
        'gutter_length' => 'Comprimento da Caleira',

        // ── Generic ──
        'area_size' => 'Tamanho da Área',
        'photos' => 'Fotos',
        'documents' => 'Documentos',
        'service_type' => 'Tipo de Serviço',
        'house_type' => 'Tipo de Moradia',
        'property_type' => 'Tipo de Propriedade',

        // ── Cross-service ──
        'material_supplied' => 'Fornecimento de Material',
        'painting_subtype' => 'Tipo de Pintura',
        'property_occupied' => 'Propriedade Ocupada',
        'has_project_plan' => 'Tem Projeto',
    ],

    'descriptions' => [
        'contact_name' => 'Nome do cliente.',
        'phone' => 'Contacto telefónico.',
        'email' => 'Email do cliente.',
        'property_address' => 'Morada da propriedade.',
        'postal_code' => 'Código postal (0000-000).',
        'notes' => 'Notas ou informações extra.',

        'roof_type' => 'Material do telhado.',
        'problem_type' => 'Tipo de problema.',
        'urgency' => 'Grau de urgência.',
        'insurance_claim' => 'Sinistro de seguro?',
        'roof_age' => 'Idade do telhado.',
        'roof_size' => 'Dimensão do telhado.',
        'leak_location' => 'Local da infiltração.',
        'asbestos_removal_required' => 'Remoção de amianto?',

        'paint_scope' => 'Interior, exterior ou ambos.',
        'surface_condition' => 'Estado das superfícies.',
        'building_type' => 'Tipo de edifício.',

        'remodel_scope' => 'Divisão a remodelar.',
        'timeline' => 'Prazo da obra.',
        'budget_range' => 'Intervalo de orçamento.',

        'terrace_type' => 'Tipo de terraço.',
        'current_surface' => 'Revestimento atual.',

        'insulation_type' => 'Tipo de isolamento.',
        'current_insulation' => 'Isolamento existente.',

        'work_type' => 'Tipo de trabalho.',
        'surface_type' => 'Tipo de superfície.',

        'facade_material' => 'Material da fachada.',
        'building_height' => 'Altura do edifício.',
        'access_type' => 'Tipo de acesso.',
        'access_method' => 'Método de acesso.',

        'gutter_material' => 'Material da caleira.',
        'gutter_length' => 'Comprimento da caleira.',

        'area_size' => 'Tamanho da área.',
        'photos' => 'Fotos do local.',
        'documents' => 'Documentos, projetos ou especificações.',
        'service_type' => 'Serviço selecionado.',
        'house_type' => 'Tipo de moradia.',
        'property_type' => 'Tipo de propriedade.',

        'material_supplied' => 'Quem fornece os materiais.',
        'painting_subtype' => 'Subtipo de serviço de pintura.',
        'property_occupied' => 'A propriedade está ocupada durante a obra.',
        'has_project_plan' => 'Já tem um projeto ou plano definido.',
    ],
];
