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

- ユーザーが「興味のあるカテゴリを選択して登録する」画面を考えます
- カテゴリは任意の数選択できますが、最低一つは選択しなければなりません
- この時のバリデーション・エラーデザイン・データ登録の方法を以下に書いていきます

## 1. テーブルを作成する

- users・user_category・categoriesの3テーブルを使用します
  - Usersテーブルは既存のものを使用します
  - user_category, categoriesを以下で追加します

```zsh
php artisan make:migration create_categories_table
php artisan make:migration create_user_category_table
```

```php:categories_table.php
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }
```

```php:user_category_table.php
    public function up()
    {
        Schema::create('user_category', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }
```

`php artisan migrate`できたらOKです

## 2. モデルにリレーションを追記する

## 3. 表示画面を作成する

## 4. 登録処理を作成する

## 5. バリデーションを作成する

## 6. エラーデザインを記述する