<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition()
    {
        $wallet = Wallet::factory()->create();

        return [
            'wallet_id' => $wallet->id,
            'type' => $this->faker->randomElement(['deposit', 'transfer']),
            'amount' => $this->faker->randomFloat(2, 0, 1000),
            'recipient_user_id' => function () {
                return Wallet::factory()->create()->user_id;
            },
        ];
    }
}
