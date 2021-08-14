<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    public function items() {
        return $this->hasMany('App\Models\TransactionItem', 'transaction_id', 'id')->whereNull('deleted_at');
    }
}
