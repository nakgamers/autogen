<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RmManager;

class RmManagerSeeder extends Seeder
{
    public function run(): void
    {
        $managers = [
            [
                'name'         => 'Taat Tusriadi',
                'phone_number' => '081294951041',
            ],
            [
                'name'         => 'Fajri Syahiffadhillah',
                'phone_number' => '085156228066',
            ],
            [
                'name'         => 'Iksan Nurdin',
                'phone_number' => '089687255804',
            ],
            [
                'name'         => 'Yopiardet Afridonyus',
                'phone_number' => '085218721149',
            ],
            [
                'name'         => 'Rino Marianto',
                'phone_number' => '081903790367',
            ],
            [
                'name'         => 'Boby Megandy',
                'phone_number' => '082299352942',
            ],
            [
                'name'         => 'Saor Hamonangan P',
                'phone_number' => '082177250361',
            ],
            [
                'name'         => 'Imaddudin',
                'phone_number' => '087770624848',
            ],
            [
                'name'         => 'Dwi Rama Joko Prasetyo',
                'phone_number' => '081992252685',
            ],
            [
                'name'         => 'Khoiril Anwar',
                'phone_number' => '081284740540',
            ],
            [
                'name'         => 'Danu Dwi Riyanto',
                'phone_number' => '081382063499',
            ],
            [
                'name'         => 'Yana Supriatna',
                'phone_number' => '081212848837',
            ],
            [
                'name'         => 'Andi Komara',
                'phone_number' => '081287776736',
            ],
            [
                'name'         => 'Rengga Fradana',
                'phone_number' => '081906368651',
            ],
            [
                'name'         => 'Hardian Sugito',
                'phone_number' => '08889213804',
            ],
            [
                'name'         => 'Demas Brahmanto Putro',
                'phone_number' => '081217706440',
            ],
            [
                'name'         => 'Muhammad Taufan',
                'phone_number' => '085157738117',
            ],
            [
                'name'         => 'Rizky Adjie Rachmadi',
                'phone_number' => '082247528848',
            ],
            [
                'name'         => 'Sqatya Wibrahma',
                'phone_number' => '081218172790',
            ],
            [
                'name'         => 'M.Suhendi Saputra',
                'phone_number' => '089512547663',
            ],
            [
                'name'         => 'Muhammad Ramli',
                'phone_number' => '08982774980',
            ],
            [
                'name'         => 'Moehammad Anugerah P',
                'phone_number' => '085693661626',
            ],
            [
                'name'         => 'Dian Evendi',
                'phone_number' => '081358476993',
            ],
        ];

        foreach ($managers as $manager) {
            RmManager::updateOrCreate(
                ['name' => $manager['name']],
                $manager
            );
        }
    }
}