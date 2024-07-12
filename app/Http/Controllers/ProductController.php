<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // This method will show products page
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')->get();
        
        return view('products.list', [
            'products' => $products
        ]);
    }

    // This method will create products
    public function create()
    {
        return view('products.create');
    }

    // This method will show create product page
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|min:5',
            'sku' => 'required|min:3',
            'price' => 'required|numeric'
        ];

        if($request->image != "") 
        {
            $rules['image'] = 'image';
        }
        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return redirect()->route('products.create')->withInput()->withErrors($validator);
        }

        // Inserting product into the database
        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if($request->image != "") 
        {
            // Storing Image
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time(). ''.$ext; 

            // Save image to products directory
            $image->move(public_path('uploads/products'), $imageName);

            // Save image name in database
            $product->image = $imageName;
            $product->save();
        }
        
        return redirect()->route('products.index')->with('success', 'Product added successfully.');
    }

    // This method will show edit product page
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', [
            'product' => $product
        ]);
    }

    // This method will update the products list
    public function update($id, Request $request)
    {
        $product = Product::findOrFail($id);

        $rules = [
            'name' => 'required|min:5',
            'sku' => 'required|min:3',
            'price' => 'required|numeric'
        ];

        if($request->image != "") 
        {
            $rules['image'] = 'image';
        }
        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()) {
            return redirect()->route('products.edit', $product->id)->withInput()->withErrors($validator);
        }

        // updating product into the database
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->save();

        if($request->image != "") 
        {
            // Delete old image
            File::delete(public_path('uploads/products/'.$product->image));

            // Storing Image
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time(). ''.$ext; 

            // Save image to products directory
            $image->move(public_path('uploads/products'), $imageName);

            // Save image name in database
            $product->image = $imageName;
            $product->save();
        }
        
        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    // This method will delete the product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete image
        File::delete(public_path('uploads/products/'.$product->image));

        // Delete product from database
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
        
    }
}
