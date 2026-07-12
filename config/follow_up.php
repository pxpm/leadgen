<?php

/**
 * Follow-Up Communication Configuration.
 *
 * Defines scenarios, reasons, prompts, and subject lines for AI-powered
 * email composition. Locale-aware — add 'en' blocks for English support.
 */

return [

    'scenarios' => [
        'decline' => [
            'label' => 'Rejeitar Lead',
            'icon' => 'heroicon-o-x-circle',
            'groups' => [
                'availability' => [
                    'label' => 'Indisponibilidade',
                    'reasons' => [
                        'no_availability' => 'Sem disponibilidade de agenda',
                        'already_booked' => 'Outro projeto em curso',
                    ],
                ],
                'scope' => [
                    'label' => 'Ambito do trabalho',
                    'reasons' => [
                        'job_too_small' => 'Trabalho demasiado pequeno',
                        'job_too_big' => 'Trabalho demasiado grande',
                    ],
                ],
                'location' => [
                    'label' => 'Localizacao',
                    'reasons' => [
                        'out_of_area' => 'Fora da area de servico',
                    ],
                ],
                'specialty' => [
                    'label' => 'Especialidade',
                    'reasons' => [
                        'not_specialty' => 'Nao e a minha especialidade',
                    ],
                ],
                'budget' => [
                    'label' => 'Orcamento',
                    'reasons' => [
                        'budget_mismatch' => 'Orcamento nao compativel',
                    ],
                ],
                'other' => [
                    'label' => 'Outro motivo',
                    'reasons' => [
                        'other' => 'Outro',
                    ],
                ],
            ],
            'locales' => [
                'pt' => [
                    'subject' => 'Sobre o seu pedido de orçamento — :tenant',
                    'ai_system_prompt' => 'És um empreiteiro profissional em Portugal. Vais escrever um email educado a rejeitar um pedido de orçamento. Sê respeitoso, agradece o contacto, explica o(s) motivo(s) de forma profissional. Mantém um tom caloroso — o cliente pode voltar no futuro. Assina sempre com o nome do empreiteiro indicado no contexto. NUNCA uses formatação (negrito, itálico, asteriscos, underscores) — isto é texto simples, não Markdown. NUNCA inventes informações que não te foram fornecidas. Escreve em português de Portugal (pt-PT). Responde APENAS com o corpo do email, sem assunto.',

                ],
            ],
        ],

        'request_info' => [
            'label' => 'Pedir Informações',
            'icon' => 'heroicon-o-question-mark-circle',
            'reasons' => [
                'photos' => 'Fotos do local/situação',
                'exact_address' => 'Morada exata',
                'dimensions' => 'Medidas/área (m²)',
                'budget' => 'Expectativa de orçamento',
                'timeline' => 'Prazo desejado',
                'access_details' => 'Detalhes de acesso ao local',
                'previous_work' => 'Trabalhos anteriores realizados',
                'material_preference' => 'Preferência de materiais',
                'other' => 'Outra informação',
            ],
            'locales' => [
                'pt' => [
                    'subject' => 'Informações adicionais para o seu orçamento',
                    'ai_system_prompt' => 'És um empreiteiro profissional em Portugal. Vais escrever um email a pedir informações adicionais a um potencial cliente. Sê específico sobre o que precisas e explica porque essa informação ajuda a fornecer um orçamento mais preciso. Mantém um tom amigável e colaborativo. Assina sempre com o nome do empreiteiro indicado no contexto. NUNCA uses formatação (negrito, itálico, asteriscos, underscores) — isto é texto simples, não Markdown. NUNCA inventes informações. Escreve em português de Portugal (pt-PT). Responde APENAS com o corpo do email, sem assunto.',
                ],
            ],
        ],

        'quote_followup' => [
            'label' => 'Acompanhar Orçamento',
            'icon' => 'heroicon-o-clock',
            'reasons' => [
                'first_followup' => 'Primeiro acompanhamento (3 dias)',
                'second_followup' => 'Segundo acompanhamento (7 dias)',
                'final_followup' => 'Último contacto (14 dias)',
            ],
            'locales' => [
                'pt' => [
                    'subject' => 'Acompanhamento do seu orçamento',
                    'ai_system_prompt' => 'És um empreiteiro profissional em Portugal. Vais escrever um email de acompanhamento sobre um orçamento enviado. O tom depende do estágio: primeiro acompanhamento é suave, o segundo é ligeiramente mais direto, o terceiro é um último contacto. Nunca sejas insistente — respeita a decisão do cliente. Assina sempre com o nome do empreiteiro indicado no contexto. NUNCA uses formatação (negrito, itálico, asteriscos, underscores) — isto é texto simples, não Markdown. NUNCA inventes informações. Escreve em português de Portugal (pt-PT). Responde APENAS com o corpo do email, sem assunto.',
                ],
            ],
        ],

        'general' => [
            'label' => 'Contacto Geral',
            'icon' => 'heroicon-o-chat-bubble-left',
            'reasons' => [],
            'locales' => [
                'pt' => [
                    'subject' => 'Contacto — :tenant',
                    'ai_system_prompt' => 'És um empreiteiro profissional em Portugal. Vais escrever um email de contacto geral para um potencial cliente. Toma o tópico e notas fornecidos e transforma-os num email profissional, caloroso e conciso. Assina sempre com o nome do empreiteiro indicado no contexto. NUNCA uses formatação (negrito, itálico, asteriscos, underscores) — isto é texto simples, não Markdown. NUNCA inventes informações. Escreve em português de Portugal (pt-PT). Responde APENAS com o corpo do email, sem assunto.',
                ],
            ],
        ],
    ],

    'ai' => [
        'provider' => 'deepseek',
        'model' => 'deepseek-chat',
        'max_tokens' => 500,
        'temperature' => 0.7,
    ],
];
