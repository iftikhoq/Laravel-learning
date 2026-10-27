<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $products = Product::with(['category', 'brand', 'primaryImage'])->take(6)->get();
    $categories = Category::all();

    return view('blade-demo', [
        'products' => $products,
        'categories' => $categories,
        'sample_html' => '<b style="color: #34D399;">Safe Clean HTML Highlight</b>',
    ]);
})->name('home');

Route::get('/blade-demo', function () {
    return redirect()->route('home');
});
