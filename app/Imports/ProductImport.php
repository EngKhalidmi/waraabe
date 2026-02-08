<?php

namespace App\Imports;

use App\Models\Products;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Products([
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'actual_price' => $row['actual_price'],
            'selling_price' => $row['selling_price'],
            'total_actual_price' => $row['total_actual_price'],
            'total_selling_price' => $row['total_selling_price'],
            'category' => $row['category'],
            'purchased_from' => $row['purchased_from'],
        ]);
    }
}
