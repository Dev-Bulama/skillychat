<?php

namespace Database\Seeders\Core;

use App\Enums\StatusEnum;
use App\Models\Core\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!(Language::where('code','en')->exists())){

            Language::firstOrCreate(['code'=> 'en',],[
                'name'          => 'United States',
                'display_name'  => 'English',
                'created_by'    => get_superadmin()->id,
                'is_default'    => StatusEnum::true->status(),
                'status'        => StatusEnum::true->status(),
            ]);
        }
    }
}
