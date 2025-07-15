<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\UserRating;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\AddressRequest;

class ProfileController extends Controller
{
    public function showMypage(Request $request)
    {
        // ユーザー情報を取得
        $user = Auth::user();
        
        // タブ選択（デフォルトは出品した商品）
        $activeTab = $request->query('tab', 'selling');
        
        // 出品した商品を取得
        $sellingItems = Item::where('seller_id', $user->id)->get();
        
        // 購入した商品の取得
        $purchasedItems = Purchase::where('user_id', $user->id)
                                        ->with('item')
                                        ->get()
                                        ->pluck('item');

        // 追加：取引中商品を取得
        $transactions = Transaction::where(function($query) use ($user) {
            $query->where('seller_id', $user->id)
                  ->orWhere('buyer_id', $user->id);
        })
        ->where('status', 'in_progress')
        ->with(['item', 'messages'])
        ->orderBy('updated_at', 'desc')
        ->get();

        // ★ 追加：各取引の未読メッセージ数を計算
        $transactions->each(function($transaction) use ($user) {
            $transaction->unread_messages_count = $transaction->getUnreadMessagesCount($user->id);
        });

        // ★ 追加：未読取引数を計算（タブのバッジ用）
        $unreadTransactionsCount = $transactions->filter(function($transaction) {
            return $transaction->unread_messages_count > 0;
        })->count();

        // ★ 追加：ユーザーの評価平均を計算
        $averageRating = UserRating::where('rated_user_id', $user->id)->avg('rating') ?? 0;
        $averageRating = round($averageRating); // 四捨五入
        
        return view('auth.mypage', compact(
            'user', 
            'activeTab', 
            'sellingItems', 
            'purchasedItems',
            'transactions',           // ★ 追加
            'unreadTransactionsCount', // ★ 追加
            'averageRating'           // ★ 追加
        ));
    }

    /**
     * プロフィール編集画面を表示
     */
    public function edit()
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    /**
     * プロフィール情報を更新
     */
    public function update(UpdateProfileRequest $request)
    {
    
    $user = Auth::user();
    
    // 入力データで更新
    $user->name = $request->name;
    $user->postal_code = $request->postal_code;
    $user->address = $request->address;
    $user->building_name = $request->building_name;

    // アバター画像のアップロード処理
    if ($request->hasFile('avatar')) {
        // 古いアバター画像が存在する場合は削除
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        // storage/app/publicディレクトリに保存
        $path = $request->file('avatar')->store('avatars', 'public');
        
        // データベースにはパスを保存
        $user->avatar = $path;
    }

    // 変更を保存
    $user->save();

    // マイページにリダイレクトして成功メッセージを表示
    return redirect()->route('mypage')->with('success', 'プロフィールを更新しました');
    }

    /*** 住所編集画面を表示*/   
    public function editAddress(Request $request)
   {
    $user = Auth::user();
    $itemId = $request->query('item_id');
    $item = null;

    if($itemId){
        $item =Item::find($itemId);
    }
    return view('auth.address_edit', compact('user','item'));
   }

/**
 * 住所情報を更新
 */
public function updateAddress(AddressRequest $request)
{
    $user = Auth::user();
    
    
    // 入力データで更新
    $user->postal_code = $request->postal_code;
    $user->address = $request->address;
    $user->building_name = $request->building_name;

    // 変更を保存
    $user->save();
    //商品IDが存在すれば購入ページにリダイレクト
    if($request->item_id){
        return redirect()->route('products.confirm',$request->item_id)->with('success','住所情報を更新しました');
    }

    return redirect()->route('mypage')->with('success','住所情報を更新しました');
}
}