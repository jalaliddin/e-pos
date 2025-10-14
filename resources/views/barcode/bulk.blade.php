<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Label Print (15x50 mm)</title>
    <style>
        /* Printer: 203 DPI, Label: 50x15 mm */
        @page {
            size: 50mm 15mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            background: #fff;
        }

        .label {
            width: 50mm;
            height: 15mm;
            box-sizing: border-box;
            border: 0.2mm solid #000;
            margin: 1mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-family: "Arial", sans-serif;
            overflow: hidden;
        }

        .name {
            font-size: 6pt;
            font-weight: bold;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 46mm;
        }

        img {
            width: 46mm;
            height: auto;
            margin: 0.3mm 0;
        }

        .code {
            font-size: 6pt;
            line-height: 1;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .label {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
@php
    use Milon\Barcode\DNS1D;
    $barcode = new DNS1D();
@endphp

@foreach ($products as $product)
    <div class="label">
        <div class="name">{{ $product->name }}</div>
        <img src="data:image/png;base64,{{ $barcode->getBarcodePNG($product->barcode, 'C128') }}" alt="barcode">
        <div class="code">{{ $product->barcode }}</div>
    </div>
@endforeach

<script>
    window.onload = () => window.print();
</script>
</body>
</html>
