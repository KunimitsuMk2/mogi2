@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{asset('css/auth/address_edit.css')}}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h1>住所の変更</h1>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('address.update') }}">
                        @csrf
                        <input type="hidden" name="item_id" value="{{ $item->id ?? '' }}">


                        <div class="form-group mb-4">
                            <label for="postal_code">郵便番号</label>
                            <input id="postal_code" type="text" class="form-control" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}">
                            @error('postal_code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="address">住所</label>
                            <input id="address" type="text" class="form-control" name="address" value="{{ old('address', $user->address) }}">
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="building_name">建物名</label>
                            <input id="building_name" type="text" class="form-control" name="building_name" value="{{ old('building_name', $user->building_name) }}">
                            @error('building_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary update-button">
                                更新する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection