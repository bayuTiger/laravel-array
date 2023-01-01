@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">ユーザーにカテゴリを設定します</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        @if (session('categories'))
                            <div class="alert alert-success" role="alert">
                                登録されているカテゴリー↓
                                <ul>
                                    @foreach (session('categories') as $row)
                                        <li>{{ $row->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <h3>{{ Auth::user()->name }}さん　興味のあるカテゴリを以下から選択してください</h3>

                        <form method="POST" action="{{ route('store') }}">
                            @csrf
                            <fieldset class="row mb-3">
                                <legend class="col-md-4 col-form-label text-md-end">カテゴリー</legend>
                                <div class="col-sm-5 col-form-label">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('categories') is-invalid @enderror"
                                            type="checkbox" name="categories[]" id="frontend" value=1>
                                        <label class="form-check-label" for="frontend">
                                            フロントエンド
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('categories') is-invalid @enderror"
                                            type="checkbox" name="categories[]" id="backend" value=2>
                                        <label class="form-check-label" for="backend">
                                            バックエンド
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input @error('categories') is-invalid @enderror"
                                            type="checkbox" name="categories[]" id="infra" value=3>
                                        <label class="form-check-label" for="infra">
                                            インフラ
                                        </label>
                                    </div>
                                </div>
                            </fieldset>

                            <div class="row mb-2">
                                <div class="col-md-3 offset-md-1">
                                    <button type="submit" class="btn btn-outline-primary">登録
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
