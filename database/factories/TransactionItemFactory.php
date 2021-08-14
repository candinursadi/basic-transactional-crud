<?php

namespace Database\Factories;

use App\Models\TransactionItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;

class TransactionItemFactory extends Factory
{
    protected $model = TransactionItem::class;

    public function definition(): array
    {
        $transactions = Transaction::all()->pluck('id')->toArray();

    	return [
    	    'transaction_id' => $this->faker->randomElement($transactions),
            'title' => $this->faker->word(),
            'qty' => $this->faker->numberBetween($min=1, $max=9),
            'price' => $this->faker->numberBetween($min=1000, $max=2000),
    	];
    }
}
