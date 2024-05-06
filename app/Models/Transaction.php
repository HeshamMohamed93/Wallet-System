<?php

namespace App\Models;

use App\Util\TransactionTypes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = ['wallet_id', 'type', 'amount', 'recipient_user_id'];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function recipientUser()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function getCounterpartyUserAttribute()
    {
        if ($this->type === TransactionTypes::TRANSFER) {
            return $this->recipient_user_id === Auth::id() ? $this->wallet->user : $this->recipientUser;
        }

        return null;
    }
}
