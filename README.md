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

`php artisan make:request Home/StoreRequest`で作成したファイルに記述します

```php:StoreRequest.php
<?php

namespace App\Http\Requests\Home;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'categories' => ['required']
        ];
    }
}
```

作成したバリデーションをコントローラーで呼び出します

```diff_php
- use Illuminate\Support\Request
+ use App\Http\Requests\Home\StoreRequest; 

// ...

- public function store(Request $request)
+ public function store(StoreRequest $request)
```

エラーメッセージが表示されるようにViewも変更します

```php:home.blade.php
// ...
<div class="card-body">
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
```

## 6. バリデーションエラーデザインを記述する

- 例えば他にも入力を受け付ける必須項目「一番興味のあるカテゴリ」があったとします
- この項目がエラーとなって返ってきた時に、**以前チェックを入れた項目が保存されません**
- 通常のinputであればoldメソッドを使うことでこの問題は解決するのですが、チェックボックスでは他の方法を探す必要があります...

バリデーションエラーだけであれば、Requestファイルに項目を追加するだけで発生させることができるので、実際に試してみましょう

```php:StoreRequest.php
return [
    'categories' => ['required'],
    'name' => ['required']
];
```

次に3つのチェックボックスそれぞれのinputタグの中に、条件によってcheckedを付与する記述を追加します

```php:home.blade.php
<div class="form-check form-check-inline">
    <input class="form-check-input @error('categories') is-invalid @enderror"
        type="checkbox" name="categories[]" id="frontend" value=1 @if (is_array(old('categories')) && in_array('1', old('categories'))) checked @endif>
    <label class="form-check-label" for="frontend">
        フロントエンド
    </label>
</div>
```

:::note info
上記はvalue=1のフロントエンドに追記する例です
他2つはそれぞれのvalueに合わせてin_arrayメソッドの第一引数を2,3と変更してください
:::