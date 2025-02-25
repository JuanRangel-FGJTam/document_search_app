<x-mail::message>
# ¡Tu Constancia de Extravío de Documentos ha sido validada!

Estimado/a {{ $fullName }},

Nos complace informarte que tu **Constancia de Extravío de Documentos** ha sido validada exitosamente. Ahora puedes descargarla y consultarla cuando lo necesites.

### 📌 Detalles de tu solicitud
- **Folio:** {{ $folio }}
- **Estatus:** {{ $status }}

### 📌 Opciones para acceder a tu documento:
1. **Desde nuestro portal**: Puedes acceder y descargar tu constancia en cualquier momento desde el siguiente enlace:
   <x-mail::button :url="'https://fiscaliadigital.fgjtam.gob.mx'">
   Consultar en Fiscalía Digital
   </x-mail::button>

2. **Adjunta en este correo**: También hemos incluido la constancia como archivo adjunto para tu comodidad.

### ℹ️ Información adicional
Si necesitas más información o tienes alguna duda, no dudes en contactarnos a través de nuestro portal.

Gracias por confiar en nosotros.

Atentamente,
**{{ config('app.name') }}**
</x-mail::message>
