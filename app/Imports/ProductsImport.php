<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {   
        $product = Product::where('name', $row['name'])->first();

        if ($product) {
            $product->quantity = $row['quantity'] ?? 0;
            $product->save();
            return null; 
        }
// name	description	barcode	price	quantity	income_price	category_id 
        return new Product([
            'name'        => $row['name'] ?? null,
            'description'        => $row['description'] ?? null,
            'barcode'     => $row['barcode'] ?? null,
            'price'       => $row['price'] ?? 0,
            'quantity'    => $row['quantity'] ?? 0,
            'income_price'       => $row['income_price'] ?? 0,
            'category_id'       => $row['category_id'] ?? 1,
        ]);
    }
}
