<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // 出品画面の表示（GET /sell）
    public function create()
    {
        $categories = Category::all(); // カテゴリ一覧取得
        $conditions = Item::$conditionNames; // 商品状態の選択肢
        return view('products.create', compact('categories', 'conditions'));
    }

    // 商品登録処理（POST /products）
    public function store(ExhibitionRequest $request)
   {

        // 画像がアップロードされている場合のみ処理
        $imagePath = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('images', 'public');
            }

        // Itemモデルを使用して商品を登録
        $item = new Item();
        $item->name = $request->input('name');
        $item->description = $request->input('description');
        $item->brand = $request->input('brand', null); // ブランド名（フォームから送信されていれば保存）
        $item->price = $request->input('price');
        $item->condition = $request->input('condition');
        $item->image_url = $imagePath ? 'storage/' . $imagePath : null;
        $item->seller_id = Auth::id();
        $item->status = 'available';
        $item->save();

        // カテゴリ登録（選択されている場合のみ）
        if ($request->has('categories')) {
            $item->categories()->attach($request->input('categories'));
        }

        return redirect()->route('products.index')->with('success', '商品を出品しました！');
        }
}