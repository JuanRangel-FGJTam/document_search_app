<x-mail::message>
# ¡Constancia de Extravío de Documentos generada!

Estimado/a {{ $fullName }}.

Nos complace informarle que su **Constancia de Extravío de Documentos** ha sido generada. Ahora puede descargarla y consultarla cuando lo necesite.

### Detalles de su solicitud

- **Folio:** {{ $folio }}
- **Estatus:** {{ $status }}

### Opciones para acceder a su documento:

- **Desde nuestro portal**: Puede acceder y descargar su constancia en cualquier momento desde su perfil de Fiscalía Digital en la sección **Mis trámites**.

<x-mail::button :url="'https://fiscaliadigital.fgjtam.gob.mx'">
Consultar en Fiscalía Digital
</x-mail::button>

- **Adjunta en este correo**: También hemos incluido la constancia como archivo adjunto para su comodidad.

### Información adicional

Si necesita más información o tiene alguna duda, no dude en contactarnos a través del número **83431861151** extensión **51010**

Gracias por confiar en nosotros.

<div style="text-align: center;">
    Atentamente,<br>
    <strong>Fiscalía General de Justicia del Estado de Tamaulipas</strong>
</div>
</x-mail::message>
