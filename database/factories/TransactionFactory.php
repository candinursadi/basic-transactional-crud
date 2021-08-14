<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $users = User::all()->pluck('id')->toArray();
        $total = $this->faker->numberBetween($min=1000, $max=9000);
        $paid = $this->faker->numberBetween($min=9000, $max=10000);
        $change = $paid - $total;
        $payment_method = ['cash','card'];

    	return [
    	    'user_id' => $this->faker->randomElement($users),
            'device_timestamp' => Carbon::now(),
            'total_amount' => $total,
            'paid_amount' => $paid,
            'change_amount' => $change,
            'payment_method' => $this->faker->randomElement(['cash','card']),
    	];
    }
}
