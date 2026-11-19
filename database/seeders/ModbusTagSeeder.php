<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModbusTagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [];
        $units = ['MPa', '℃', '%', 'L/min', 'V', 'A'];

        // 20件のタグデータを自動生成
        for ($i = 1; $i <= 20; $i++) {
            $tags[] = [
                'tag_code'         => sprintf('TAG_%03d', $i),
                'tag_name'         => "センサー機器 {$i}", // 'name' から 'tag_name' に修正
                'unit'             => $units[array_rand($units)],
                'register_address' => $i - 1, // レジスタアドレス 0から19
                'data_type'        => 'float',
                'high_limit'       => 100.00,
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        DB::table('modbus_tags')->insert($tags);
    }
}