<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WalletSeeder extends Seeder
{
    /**
     * Static PIN code for testing.
     */
    const STATIC_PIN_CODE = '123456';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $hashed_pin_code = Hash::make(self::STATIC_PIN_CODE);

            Wallet::create([
                'user_id' => $user->id,
                'balance' => rand(100, 10000),
                'pin_code' => $hashed_pin_code
            ]);
        }
    }
}
