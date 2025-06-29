<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        // タブの状態を取得（デフォルトは'recommended'）
        $tab = $request->query('tab', 'recommended');
    
        // 検索キーワードを取得
        $search = $request->query('search');
    
        // FN015: 未認証の場合はマイリストで何も表示されない
        if ($tab === 'mylist' && !Auth::check()) {
            // 空のページネーション結果を作成
            $items = new \Illuminate\Pagination\LengthAwarePaginator(
                [], // 空の配列
                0,  // 総件数0
                20, // 1ページあたりの件数
                1,  // 現在のページ
                [
                    'path' => request()->url(),
                    'query' => request()->query()
                ]
            );
            
            return view('products.index', [
                'items' => $items,
                'tab' => $tab,
                'search' => $search,
            ]);
        }
    
        // クエリのベース
        $query = Item::query();
    
        // 検索キーワードがある場合は商品名で部分一致検索
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
    
        // タブに応じてクエリを変更
        if ($tab === 'mylist' && Auth::check()) {
            // マイリスト表示の場合（ログイン済みユーザーがいいねした商品を表示）
            $query->whereHas('favorites', function($q) {
                $q->where('user_id', Auth::id());
            });
        } else {
            // おすすめ表示の場合
            // ログインしている場合、自分の出品商品を除外
            if (Auth::check()) {
                $query->where('seller_id', '!=', Auth::id());
            }
        }
    
        // 商品を取得（新着順）
        $items = $query->orderBy('created_at', 'desc')
                      ->paginate(20); // 1ページ20件表示
    
        // 検索とタブのパラメータをページネーションリンクに追加
        $items->appends([
            'search' => $search,
            'tab' => $tab
        ]);
    
        return view('products.index', [
            'items' => $items,
            'tab' => $tab,
            'search' => $search,
        ]);
    }

    public function showItem(Item $item)
    {
        // コメントとそのユーザー情報を一緒に取得
        $item->load(['comments.user', 'categories']); // ここでリレーション一気に読み込み
        return view('products.item', compact('item'));
    }
}