<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'pin_code' => bcrypt('123456'),
            'balance' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
