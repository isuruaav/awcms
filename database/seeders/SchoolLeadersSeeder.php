<?php

namespace Database\Seeders;

use App\Models\SchoolLeader;
use Illuminate\Database\Seeder;

final class SchoolLeadersSeeder extends Seeder
{
    public function run(): void
    {
        $leaders = [
            [
                'role_key' => SchoolLeader::ROLE_COMMANDANT,
                'title_en' => 'The Commandant',
                'title_si' => 'සේනාවිධායක',
                'sort_order' => 1,
            ],
            [
                'role_key' => SchoolLeader::ROLE_CHIEF_INSTRUCTOR,
                'title_en' => 'The Chief Instructor',
                'title_si' => 'ප්‍රධාන උපදේශක',
                'sort_order' => 2,
            ],
            [
                'role_key' => SchoolLeader::ROLE_WARRANT_OFFICER,
                'title_en' => 'Warrant Officer School of Signals',
                'title_si' => 'සංඥා පාසලේ බලලත් නිලධාරී',
                'sort_order' => 3,
            ],
        ];

        foreach ($leaders as $leader) {
            SchoolLeader::query()->firstOrCreate(
                [
                    'role_key' => $leader['role_key'],
                ],
                [
                    'title_en' => $leader['title_en'],
                    'title_si' => $leader['title_si'],
                    'sort_order' => $leader['sort_order'],
                    'is_active' => true,
                ],
            );
        }
    }
}
