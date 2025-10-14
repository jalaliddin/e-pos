<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Milon\Barcode\DNS1D;
use Illuminate\Http\Request;

class BarcodePrintController extends Controller
{
        public function print(Product $product)
    {
        return view('barcode.print', compact('product'));
    }
        public function printBulk($ids)
    {
        $idArray = explode(',', $ids);
        $products = Product::whereIn('id', $idArray)->get();
        return view('barcode.bulk', compact('products'));
    }
}
