<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * お気に入りに追加または削除
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function toggle(Item $item)
    {
        $user = Auth::user();
        
        // すでにお気に入りに入っているか確認
        $existing = Favorite::where('user_id', $user->id)
                          ->where('item_id', $item->id)
                          ->first();
        
        if ($existing) {
            // お気に入りから削除
            $existing->delete();
            $message = 'お気に入りから削除しました。';
        } else {
            // お気に入りに追加
            $favorite = new Favorite();
            $favorite->user_id = $user->id;
            $favorite->item_id = $item->id;
            $favorite->save();
            $message = 'お気に入りに追加しました。';
        }
        
        // Ajaxリクエストの場合はJSONで返す
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => $item->favorites()->count()
            ]);
        }
        
        // 通常のリクエストの場合はリダイレクト
        return redirect()->back()->with('success', $message);
    }
}