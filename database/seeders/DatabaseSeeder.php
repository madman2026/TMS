<?php

namespace Database\Seeders;

use App\DeviceTypeEnum;
use App\DriverTypeEnum;
use App\InternetSpeedEnum;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'phone' => '09922926708',
            'password' => '12345678',
        ]);
        Profile::factory()->for($user)->create([
            'name' => 'primary',
            'driver' => DriverTypeEnum::CHROME,
            'device' => DeviceTypeEnum::DESKTOP,
            'internet_speed' => InternetSpeedEnum::NORMAL
        ]);
    }
}
