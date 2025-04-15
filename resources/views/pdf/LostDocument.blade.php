<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Constancia de Extravío de Documentos</title>
    <style>
        :root {
            font-size: 12px;
            --main-highlight-color: #354c5c;
            --main-highlight-color2: #426aa5;
            --secondary-highlight-color: #B8E6F1;
            --list-point-color: #C2C6CA;
            --text-color: #303E48;
        }

        @page {
            margin: 0cm 0cm 0cm 0cm;
            font-family: Arial;
        }

        body {
            margin: 2.4cm 1cm 1cm 1cm;
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            color: #303E48;
            position: relative;
        }

        header {
            position: fixed;
            top: .5cm;
            left: 1cm;
            right: 1cm;
            height: 5rem;
            padding-top: .2cm;
            background-color: #111633;
            color: rgb(223, 223, 223);
            text-align: right;
            line-height: 1.5rem;
        }

        header .img.logo {
            width: auto;
            height: 4rem;
            display: block;
            position: absolute;
            top: 0.8rem;
            left: .75rem;
        }

        header h2.name {
            color: white;
            font-size: 1.5rem;
            text-align: right;
            max-width: 26rem;
            margin: 0 0 0 55%;
            line-height: 2rem;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: bold;
            text-transform: uppercase;
            color: #111633;
            border-bottom: 2px solid #111633;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            border: 1px solid #c7c7c7;
            padding: 5px;
            text-align: left;
            font-size: 9pt;
            background-color: #ffff;
        }

        .info-table th {
            padding-left: 3px;
            text-align: left;
            background-color: #eeeeee;
            color: black;
            border: 1px solid #c7c7c7;

        }

        .narrative {
            text-align: justify;
            font-size: 9pt;
        }

        .footer-note {
            font-size: 12pt;
            color: #555;
            text-align: justify;
        }

        .info-data {
            display: flex;
            justify-content: space-around
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            pointer-events: none;
            z-index: -1;
            width: 75%;
        }
    </style>
</head>

<body>
    <img class="watermark" src="{{ public_path('/images/escudo-tamps.jpg') }}" alt="Marca de agua">
    <header>
        <img class="img logo" src="{{ public_path('/images/logos/logo_fgjtam-white.png') }}">
        <div class="header-data">
            <h2 class="name">
                CONSTANCIA DE EXTRAVÍO
            </h2>
            <h2 class="name">
                DE DOCUMENTOS
            </h2>
        </div>
    </header>
    <main>
        <table class="info-table">
            <tr>
                <th style="width: 210px">Folio</th>
                <td style="width: 200px">{{ $folio }}</td>
                <th style="width: 130px">Fecha de Emisión</th>
                <td>{{ $registration_date }}</td>
            </tr>
        </table>
        <h2 class="section-title">Datos del Manifestante (Bajo protesta de decir verdad).</h2>
        <!-- AQUI QUIERO QUE LOS TH TENGAN EL MISMO WIGHT -->
        <table class="info-table">
            <tr>
                <th style="width: 210px">Manifestante</th>
                <td style="width: 200px">{{ $fullName }}</td>
                <th style="width: 130px">CURP</th>
                <td>{{ $curp }}</td>
            </tr>
            <tr>
                <th>Fecha de Nacimiento</th>
                <td>{{ $birthdateFormated }}</td>
                <th>Edad</th>
                <td>{{ $age }} años</td>

            </tr>
            <tr>
                <th>Correo Electrónico</th>
                <td>{{ $email }}</td>
                <th>Sexo</th>
                <td>{{ $genderName }}</td>
            </tr>
            <tr style="border-bottom:#bbb">
                <th>Municipio</th>
                <td colspan="3">{{ $address['municipalityName'] ?? 'No proporcionado' }}</td>
            </tr>
            <tr>
                <th>Colonia</th>
                <td colspan="3">{{ $address['colonyName'] ?? 'No proporcionado' }}</td>
            </tr>
            <tr>
                <th>Calle</th>
                <td colspan="3">{{ $address['street'] ?? 'No proporcionado' }}</td>
            </tr>
        </table>
        <div>
            <h2 class="section-title">Identificación.</h2>
            <div style="float: left; width: 30%; margin-right: 4%; padding-top: 40px;">
                <table class="info-table" style="width: 100%; border-spacing: 8px;">
                    <tr>
                        <th style="width: 100px">Documento</th>
                        <td>{{ $identification['documentTypeName'] }}</td>
                    </tr>
                    <tr>
                        <th>Folio</th>
                        <td>{{ isset($identification['folio']) ? $identification['folio'] : '' }}</td>
                    </tr>
                    <tr>
                        <th>Fecha de expiración</th>
                        <td>
                            @if (isset($identification['documentTypeId']) && $identification['documentTypeId'] == 1)
                                {{ isset($identification['valid']) ? \Carbon\Carbon::parse($identification['valid'])->format('Y') : '' }}
                            @else
                                {{ isset($identification['valid']) ? $identification['valid'] : '' }}
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div style="float: left; width: 70%; text-align: center; padding: 10px;">
                <img src="{{ $identificationPath }}" alt="Identificación"
                    style="height: 170px; display: block; margin-left: auto; margin-right: auto; object-fit: contain;">
            </div>
        </div>
        <!-- HASTA AQUI QUIERO QUE LOS TH TENGAN EL MISMO WIGHT -->
        <div style="clear: both;"></div>
        <h2 class="section-title">Lugar de Extravío.</h2>
        <table class="info-table">
            <tr>
                <th style="width: 210px">Municipio</th>
                <td style="width: 200px">{{ $dataLost['municipality'] }}</td>
                <th style="width: 130px">Fecha de Hechos</th>
                <td>{{ $dataLost['lost_date'] }}</td>
            </tr>
            <tr>
                <th>Colonia</th>
                <td colspan="3">{{ $dataLost['colonie'] }}</td>
            </tr>
            <tr>
                <th>Calle</th>
                <td colspan="3">{{ $dataLost['street'] }}</td>
            </tr>
        </table>

        <h2 class="section-title">
            @if (count($documentLost) >= 2)
                {{ count($documentLost) }} Documentos extraviados.
            @else
                {{ count($documentLost) }} Documento extraviado.
            @endif
        </h2>

        @foreach ($documentLost as $document)
            <table class="info-table" style="margin-bottom: 5px;">
                <tr>
                    <th style="width: 210px">Documento</th>
                    <td style="width: 200px">
                        {{ $document['document_type'] }}
                        @if ($document['document_type_id'] == 9)
                            | {{ $document['specification'] ?? 'Sin especificación' }}
                        @endif
                    </td>
                    <th style="width: 130px">Folio</th>
                    <td>{{ $document['document_number'] ?? 'No proporcionado' }}</td>
                </tr>
                <tr>
                    <th>Titular</th>
                    <td colspan="3">{{ $document['document_owner'] }}</td>
                </tr>
            </table>
        @endforeach
        @if (count($documentLost) >= 2)
            <div style="page-break-before: always;"></div>
        @endif

        <h2 class="section-title">Narración Breve de Extravío.</h2>
        <p class="narrative">
            {{ $dataLost['description'] }}
        </p>

        <table>
            <thead>
                <tr style="background: #eeeeee; color:black;">
                    <th>Información.</th>
                    <th>Código.</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <p class="footer-note">
                            La presente constancia no prejuzga los datos proporcionados por el manifestante bajo
                            protesta de
                            decir
                            verdad y se expide
                            a petición del interesado para los efectos legales a que haya lugar. Verifica los datos
                            ingresando
                            al portal
                            de la Fiscalía General
                            de Justicia del Estado de Tamaulipas.
                        </p>
                    </td>
                    <td>
                        <div style="display:flex; justify-content:center; text-align: center;">
                            <img src="{{ $qrUrl }}" alt="qrCode" style="max-width: 85%; height: auto;">
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <div style="float: left; width: 50%; color:#cacaca;">
                <label>fgjtam.gob.mx</label>
            </div>
            <div style="float: left; width: 50%; text-align:right; color:#cacaca;">
                <label>fiscaliadigital.fgjtam.gob.mx</label>
            </div>
        </div>

    </main>

    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
            $pdf->text(270, 815, "Pagina $PAGE_NUM de $PAGE_COUNT", $font, 8, array(0.5, 0.5, 0.5));
            ');
        }
    </script>

</body>

</html>
