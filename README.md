# Laravel8 複数のチェックボックス(任意選択) 取扱方 (ついでに中間テーブルも)

## 環境

```zsh
// envのDB接続部を書き換えておく
php artisan migrate:fresh
composer require laravel/ui
php artisan ui vue --auth
npm install
npm install resolve-url-loader@^5.0.0 --save-dev --legacy-peer-deps
composer require laravel/pint --dev
npm run dev
php artisan serve
```

## 概要

-   ユーザーが「興味のあるカテゴリを選択して登録する」画面を考えます
-   カテゴリは任意の数選択できますが、最低一つは選択しなければなりません
-   この時のバリデーション・エラーデザイン・データ登録の方法を以下に書いていきます

作成した記事↓

https://qiita.com/akitika/items/be1d399e9548dfb7fdb6