<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * コメントを投稿する
     *
     * @param  \App\Http\Requests\CommentRequest  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function store(CommentRequest $request, Item $item)
    {
        // コメントを保存
        $comment = new Comment();
        $comment->user_id = Auth::id();
        $comment->item_id = $item->id;
        $comment->content = $request->content;
        $comment->save();

        // 商品詳細ページにリダイレクト
        return redirect()->route('products.item', $item)
            ->with('success', 'コメントを投稿しました。');
    }
}