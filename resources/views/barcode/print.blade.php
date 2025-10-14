<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Barcode</title>
    <style>
        @page { size: 50mm 15mm; margin: 0; }
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        .label {
            width: 50mm;
            height: 15mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 8px;
            overflow: hidden;
        }
        img {
            width: 100%;
            height: auto;
        }
        .name {
            font-weight: bold;
            font-size: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .code {
            font-size: 7px;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .label, .label * {
                visibility: visible;
            }
            .label {
                position: absolute;
                left: 0;
                top: 0;
            }
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="name">{{ $product->name }}</div>

        @php
            use Milon\Barcode\DNS1D;
            $barcode = new DNS1D();
        @endphp

        <img src="data:image/png;base64,{{ $barcode->getBarcodePNG($product->barcode, 'C128') }}" alt="barcode" />

        <div class="code">{{ $product->barcode }}</div>
    </div>

    <script>
        window.onload = () => window.print();
    </script>
</body>
</html>
