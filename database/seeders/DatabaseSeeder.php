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
