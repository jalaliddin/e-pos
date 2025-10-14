@php
    use Milon\Barcode\DNS1D;
    $barcode = new DNS1D();
@endphp

<img src="data:image/png;base64,{{ $barcode->getBarcodePNG($getRecord()->barcode, 'C128') }}" alt="barcode" />
{{-- <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($getRecord()->barcode, 'C128') }}"> --}}
