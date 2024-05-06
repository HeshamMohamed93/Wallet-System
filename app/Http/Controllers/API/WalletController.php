<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Util\TransactionStatus;
use App\Util\TransactionTypes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function balance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pin_code' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!Hash::check($request->pin_code, $wallet->pin_code)) {
            return response()->json(['error' => 'Invalid PIN code'], 400);
        }

        return response()->json(['balance' => $wallet->balance]);
    }

    public function transactionHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $startDate = Carbon::parse($request->input('start_date', Carbon::now()->subMonths(3)->startOfDay()));
        $endDate = Carbon::parse($request->input('end_date', Carbon::now()->endOfDay()));

        if ($startDate->diffInMonths($endDate) > 3) {
            return response()->json(['errors' => 'Date range exceeds 3 months'], 400);
        }

        $user = Auth::user();

        $transactions = Transaction::with(['recipientUser', 'wallet'])
            ->where(function ($query) use ($user) {
                $query->where('recipient_user_id', $user->id)
                    ->orWhereHas('wallet', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $transactionHistory = $transactions->map(function ($transaction) use ($user) {
            switch ($transaction->type) {
                case TransactionTypes::DEPOSIT:
                    $transaction->status = TransactionStatus::DEPOSIT;
                    break;
                case TransactionTypes::TRANSFER:
                    if ($transaction->recipient_user_id === $user->id) {
                        $transaction->status = TransactionStatus::RECEIVED;
                    } else {
                        $transaction->status = TransactionStatus::SENT;
                    }
                    break;
                default:
                    $transaction->status = TransactionStatus::UNKNOWN;
                    break;
            }
            return $transaction;
        });

        return TransactionResource::collection($transactionHistory);
    }

    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $wallet = $user->wallet;
        $wallet->balance += $request->amount;
        $wallet->save();

        Transaction::create([
            'wallet_id' => $wallet->id,
            'type' => TransactionTypes::DEPOSIT,
            'amount' => $request->amount,
        ]);

        return response()->json(['message' => 'Deposit successful']);
    }

    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|gt:0',
            'pin_code' => 'required|digits:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!Hash::check($request->pin_code, $wallet->pin_code)) {
            return response()->json(['error' => 'Invalid PIN code'], 400);
        }

        if ($wallet->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient balance'], 400);
        }

        DB::beginTransaction();

        try {
            $recipientWallet = Wallet::whereHas('user', function($query) use ($request) {
                $query->where('email', $request->recipient_email);
            })->first();

            if (!$recipientWallet) {
                throw new \Exception('Recipient wallet not found');
            }

            $transferAmount = $request->amount;

            if ($transferAmount > 25) {
                $transferAmount += 2.5 + ($transferAmount * 0.1);
            }

            $wallet->balance -= $transferAmount;
            $wallet->save();

            $recipientWallet->balance += $request->amount;
            $recipientWallet->save();

            Transaction::create([
                'wallet_id' => $wallet->id,
                'recipient_user_id' => $recipientWallet->user_id,
                'type' => TransactionTypes::TRANSFER,
                'amount' => $request->amount,
            ]);

            DB::commit();

            return response()->json(['message' => 'Transfer successful']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function changePinCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_pin_code' => 'required|digits:6',
            'new_pin_code' => 'required|different:old_pin_code|digits:6',
            'confirm_new_pin_code' => 'required|same:new_pin_code',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid password'], 400);
        }

        if (!Hash::check($request->old_pin_code, $wallet->pin_code)) {
            return response()->json(['error' => 'Invalid old PIN code'], 400);
        }

        $wallet->pin_code = Hash::make($request->new_pin_code);
        $wallet->save();

        return response()->json(['message' => 'PIN code changed successfully']);
    }
}
