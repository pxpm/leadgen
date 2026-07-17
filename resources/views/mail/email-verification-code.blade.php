# Verificação de Email — Lead Intake Assistant

Olá,

Foi adicionada a conta de email **{{ $account->email }}** ao teu painel Lead Intake Assistant.

Clica no botão abaixo para verificares que este email te pertence:

@component('mail::button', ['url' => $verificationUrl])
Verificar Email
@endcomponent

Ou copia e cola este link no teu navegador:

{{ $verificationUrl }}

Este link expira em **15 minutos**.

Se não foste tu que adicionaste esta conta, por favor ignora este email.

---
Lead Intake Assistant
