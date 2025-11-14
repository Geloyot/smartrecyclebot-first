<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $types  = ['bin', 'user', 'system'];
        $levels = ['info', 'warning', 'error'];

        // Get all user IDs
        $userIds = User::pluck('id')->toArray();

        // Insert 20 example notifications
        for ($i = 0; $i < 20; $i++) {
            // 50% chance to be global (null), 50% to be user-specific
            $userId = $faker->boolean(50)
                ? null
                : $faker->randomElement($userIds);

            Notification::create([
                'user_id'    => $userId,
                'type'       => $faker->randomElement($types),
                'title'      => ucfirst($faker->words(3, true)),
                'message'    => $faker->sentence(8),
                'level'      => $faker->randomElement($levels),
                'is_read'    => $faker->boolean(40),  // ~40% are already read
                'created_at' => $faker->dateTimeBetween('-30 days', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
