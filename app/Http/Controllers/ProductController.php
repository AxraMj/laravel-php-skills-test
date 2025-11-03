<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private $dataFile = 'products.json';

    public function index()
    {
        return view('products.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $products = $this->getProducts();
        
        $product = [
            'id' => uniqid(),
            'product_name' => $request->product_name,
            'quantity' => (int)$request->quantity,
            'price' => (float)$request->price,
            'datetime_submitted' => now()->toDateTimeString(),
            'total_value' => (float)$request->quantity * (float)$request->price,
        ];

        $products[] = $product;
        $this->saveProducts($products);

        return response()->json([
            'success' => true,
            'message' => 'Product added successfully',
            'product' => $product,
        ]);
    }

    public function getAll()
    {
        $products = $this->getProducts();
        
        // Sort by datetime_submitted descending (newest first)
        usort($products, function($a, $b) {
            return strtotime($b['datetime_submitted']) - strtotime($a['datetime_submitted']);
        });

        // Calculate sum total
        $sumTotal = array_sum(array_column($products, 'total_value'));

        return response()->json([
            'products' => $products,
            'sum_total' => $sumTotal,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $products = $this->getProducts();
        $found = false;

        foreach ($products as &$product) {
            if ($product['id'] === $id) {
                $product['product_name'] = $request->product_name;
                $product['quantity'] = (int)$request->quantity;
                $product['price'] = (float)$request->price;
                $product['total_value'] = (float)$request->quantity * (float)$request->price;
                $found = true;
                break;
            }
        }

        if (!$found) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $this->saveProducts($products);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
        ]);
    }

    private function getProducts()
    {
        $filePath = storage_path('app/' . $this->dataFile);
        
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        $products = json_decode($content, true);

        return $products ?: [];
    }

    private function saveProducts($products)
    {
        $filePath = storage_path('app/' . $this->dataFile);
        file_put_contents($filePath, json_encode($products, JSON_PRETTY_PRINT));
        
        // Also save as XML
        $this->saveProductsAsXML($products);
    }

    private function saveProductsAsXML($products)
    {
        $xmlFile = storage_path('app/products.xml');
        $xml = new \SimpleXMLElement('<products></products>');
        
        foreach ($products as $product) {
            $item = $xml->addChild('product');
            $item->addChild('id', htmlspecialchars($product['id']));
            $item->addChild('product_name', htmlspecialchars($product['product_name']));
            $item->addChild('quantity', $product['quantity']);
            $item->addChild('price', $product['price']);
            $item->addChild('datetime_submitted', htmlspecialchars($product['datetime_submitted']));
            $item->addChild('total_value', $product['total_value']);
        }
        
        $dom = new \DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        file_put_contents($xmlFile, $dom->saveXML());
    }
}

