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
        'contact_name' => 'Name',
        'phone' => 'Phone',
        'email' => 'Email',
        'property_address' => 'Address',
        'postal_code' => 'Postal Code',
        'notes' => 'Notes',

        // ── Roofing ──
        'roof_type' => 'Roof Type',
        'problem_type' => 'Problem Type',
        'urgency' => 'Urgency',
        'insurance_claim' => 'Insurance Claim',
        'roof_age' => 'Roof Age',
        'roof_size' => 'Roof Size',
        'leak_location' => 'Leak Location',
        'asbestos_removal_required' => 'Asbestos Removal Required',

        // ── Painting ──
        'paint_scope' => 'Painting Scope',
        'surface_condition' => 'Surface Condition',
        'building_type' => 'Building Type',

        // ── Remodeling ──
        'remodel_scope' => 'Remodeling Scope',
        'timeline' => 'Timeline',
        'budget_range' => 'Budget Range',

        // ── Terraces ──
        'terrace_type' => 'Terrace Type',
        'current_surface' => 'Current Surface',

        // ── Insulation ──
        'insulation_type' => 'Insulation Type',
        'current_insulation' => 'Current Insulation',

        // ── Waterproofing ──
        'work_type' => 'Work Type',
        'surface_type' => 'Surface Type',

        // ── Facades ──
        'facade_material' => 'Facade Material',
        'building_height' => 'Building Height',
        'access_type' => 'Access Type',
        'access_method' => 'Access Method',

        // ── Gutters ──
        'gutter_material' => 'Gutter Material',
        'gutter_length' => 'Gutter Length',

        // ── Generic ──
        'area_size' => 'Area Size',
        'photos' => 'Photos',
        'documents' => 'Documents',
        'service_type' => 'Service Type',
        'house_type' => 'House Type',
        'property_type' => 'Property Type',

        // ── Cross-service ──
        'material_supplied' => 'Material Supplied',
        'painting_subtype' => 'Painting Subtype',
        'property_occupied' => 'Property Occupied',
        'has_project_plan' => 'Has Project Plan',
    ],

    'descriptions' => [
        'contact_name' => 'Client name.',
        'phone' => 'Phone contact.',
        'email' => 'Client email.',
        'property_address' => 'Property address.',
        'postal_code' => 'Postal code (0000-000).',
        'notes' => 'Notes or extra information.',

        'roof_type' => 'Roof material.',
        'problem_type' => 'Type of problem.',
        'urgency' => 'Urgency level.',
        'insurance_claim' => 'Insurance claim?',
        'roof_age' => 'Age of roof.',
        'roof_size' => 'Roof size.',
        'leak_location' => 'Location of leak.',
        'asbestos_removal_required' => 'Asbestos removal needed?',

        'paint_scope' => 'Interior, exterior, or both.',
        'surface_condition' => 'Condition of surfaces.',
        'building_type' => 'Building type.',

        'remodel_scope' => 'Area to remodel.',
        'timeline' => 'Project timeline.',
        'budget_range' => 'Budget range.',

        'terrace_type' => 'Terrace type.',
        'current_surface' => 'Current surface material.',

        'insulation_type' => 'Type of insulation.',
        'current_insulation' => 'Existing insulation.',

        'work_type' => 'Type of work.',
        'surface_type' => 'Surface type.',

        'facade_material' => 'Facade material.',
        'building_height' => 'Building height.',
        'access_type' => 'Access type.',
        'access_method' => 'Access method.',

        'gutter_material' => 'Gutter material.',
        'gutter_length' => 'Gutter length.',

        'area_size' => 'Area size.',
        'photos' => 'Photos of the site.',
        'documents' => 'Documents, plans, or specifications.',
        'service_type' => 'Selected service.',
        'house_type' => 'House type.',
        'property_type' => 'Property type.',

        'material_supplied' => 'Who supplies the materials.',
        'painting_subtype' => 'Painting service subtype.',
        'property_occupied' => 'Property is occupied during work.',
        'has_project_plan' => 'Has a defined project or plan.',
    ],
];
