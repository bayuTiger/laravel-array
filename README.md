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

## 1. テーブルを作成する

-   users・user_category・categories の 3 テーブルを使用します
    -   Users テーブルは既存のものを使用します
    -   user_category, categories を以下で追加します

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

`php artisan migrate`できたら OK です

最後にcategoriesのサンプルデータだけseederで投入します

```zsh
php artisan make:seeder CategorySeeder
```

Seederを書いていきます

```php:CategorySeeder.php
<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name' => 'Frontend',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Backend',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Infra',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];
            Category::insert($categories);
        
    }
}
```

```php:DatabaseSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (config('app.env') === 'production') {
            Log::error('本番環境でSeederの一括実行はできません。処理を終了します。');

            return;
        }
        $this->call([
            CategorySeeder::class,
        ]);
    }
}
```

`php artisan db:seed`をして成功したら完了です！

## 2. モデルにリレーションを追記する

まず category のモデルを作成します

```zsh
php artisan make:model Category
```

User モデルと Category モデルにリレーションを定義します

```php:User.php
// ...
    public function categories() // <- 新しく追加
    {
        return $this->belongsToMany(Category::class, 'user_category')->withTimeStamps();
    }
```

```php:Category.php
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_category')->withTimestamps();
    }
}
```

## 3. 表示画面を作成する

既存の home.blade.php を編集する形で画面を作成します

```php:home.blade.php
// ...
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
// ...
```

次に Routing を追加します

```php:web.php
// ...
Route::post('/store', [App\Http\Controllers\HomeController::class, 'store'])->name('store');
```

## 4. 登録処理を作成する

```php:HomeController.php
    public function store(Request $request)
    {
        if (is_array($request->categories)) {
            User::find(Auth::user()->id)->categories()->sync($request->categories);
        }

        $categories = Auth::user()->categories;
        $status = 'カテゴリーを登録しました！';

        return redirect()->route('home')->with(compact('status', 'categories'));
    }
```

## 5. バリデーションを作成する

最低1つは選択してもらうようにします


## 6. エラーデザインを記述する
