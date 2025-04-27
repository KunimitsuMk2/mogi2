@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{asset('css/products/create.css')}}">
@endsection

@section('content')
<div class="main-container">
    <h2 class="page-title">商品の出品</h2>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="product-form-container">
        @csrf
            <!-- バリデーションエラー表示 -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        {{-- 商品画像 --}}
        <div class="form-section">
            <h3 class="section-title">商品画像</h3>
            <div class="image-upload-container">
                <input type="file" name="image" id="image" class="image-input">
                <div class="image-upload-box">
                    <button type="button" class="image-upload-btn">画像を選択する</button>
                </div>
                @error('image')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-section">
            <h3 class="section-title">商品の詳細</h3>
            
            {{-- カテゴリー --}}
            <div class="form-group">
                <label class="form-label">カテゴリー</label>
                <div class="category-tags">
                    @foreach($categories as $category)
                        <label class="category-tag">
                            <input type="checkbox" name="categories[]" value="{{ $category->id }}"> {{ $category->name }}
                        </label>
                    @endforeach
                </div>
                @error('categories')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- 商品の状態 --}}
            <div class="form-group">
                <label for="condition" class="form-label">商品の状態</label>
                    <div class="select-wrapper">
                        <select name="condition" id="condition" class="form-select">
                            <option value="">選択してください</option>
                                @foreach($conditions as $value => $name)
                            <option value="{{ $value }}">{{ $name }}</option>
                                @endforeach
                        </select>
                    </div>
                     @error('condition')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
            </div>

        <div class="form-section">
            <h3 class="section-title">商品名と説明</h3>
            
            {{-- 商品名 --}}
            <div class="form-group">
                <label for="name" class="form-label">商品名</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-input">
                @error('name')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- ブランド名 --}}
            <div class="form-group">
                <label for="brand" class="form-label">ブランド名</label>
                <input type="text" name="brand" id="brand" value="{{ old('brand') }}" class="form-input">
                @error('brand')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- 商品説明 --}}
            <div class="form-group">
                <label for="description" class="form-label">商品の説明</label>
                <textarea name="description" id="description" class="form-textarea">{{ old('description') }}</textarea>
                @error('description')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror

            </div>
        </div>

        {{-- 価格 --}}
        <div class="form-section">
            <h3 class="section-title">販売価格</h3>
            <div class="form-group price-group">
                <label for="price" class="form-label">販売価格</label>
                <div class="price-input-container">
                    <span class="currency-symbol">¥</span>
                    <input type="number" name="price" id="price" value="{{ old('price') }}" class="form-input price-input">
                </div>
                @error('price')
                <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- 出品ボタン --}}
        <div class="form-action">
            <button type="submit" class="submit-btn">出品する</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 画像アップロードボタンの処理
        const uploadBtn = document.querySelector('.image-upload-btn');
        const fileInput = document.querySelector('.image-input');
        const uploadBox = document.querySelector('.image-upload-box');
        
        // 画像プレビュー用のコンテナを追加
        const previewContainer = document.createElement('div');
        previewContainer.className = 'image-preview';
        previewContainer.style.display = 'none';
        previewContainer.style.marginTop = '10px';
        previewContainer.style.maxWidth = '100%';
        previewContainer.style.height = '200px';
        previewContainer.style.backgroundColor = '#f8f9fa';
        previewContainer.style.display = 'flex';
        previewContainer.style.alignItems = 'center';
        previewContainer.style.justifyContent = 'center';
        uploadBox.after(previewContainer);
        
        // ボタンクリックでファイル選択ダイアログを開く
        uploadBtn.addEventListener('click', function() {
            fileInput.click();
        });
        
        // ファイル選択時の処理
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // 選択されたファイル名を表示
                uploadBtn.textContent = this.files[0].name;
                
                // 画像プレビュー表示
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.style.display = 'flex';
                    previewContainer.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 100%; object-fit: contain;">`;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
@endsection