<x-mail::message>
# ❌ Tu solicitud de Constancia de Extravío de Documentos ha sido cancelada

Estimado/a {{ $fullName }},

Lamentamos informarte que tu **solicitud de Constancia de Extravío de Documentos** ha sido cancelada.

### 📌 Detalles de tu solicitud
- **Folio:** {{ $folio }}
- **Motivo:** {{ $status }}
- **Observaciones:** {{$observations}}

Si aún necesitas este documento, puedes realizar una nueva solicitud en nuestro portal de manera rápida y sencilla.

### 🔄 Realiza una nueva solicitud
Para generar una nueva constancia, visita el siguiente enlace y sigue los pasos indicados:

<x-mail::button :url="'https://fiscaliadigital.fgjtam.gob.mx'">
Hacer nueva solicitud
</x-mail::button>

Si tienes dudas o necesitas asistencia, no dudes en contactarnos a través de nuestro portal.

Gracias por tu comprensión.

Atentamente,
**{{ config('app.name') }}**
</x-mail::message>
