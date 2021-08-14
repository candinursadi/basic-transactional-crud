<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use DB;

class TransactionController extends Controller
{
    public function index(Request $request){
        try{
            $result = [];
            $url = "/".$request->path()."?page=";
            $skip=0;
            $take=10;
            if($request->page > 0) $skip = ($request->page - 1) * $take;

            $transactions = Transaction::query()
                ->where('user_id', $request->user_id)
                ->whereNull('deleted_at')
                ->orderBy('id','desc');

            $transactions_next = $transactions;
            $transactions = $transactions->skip($skip)->take($take)->get();
            $transactions_next = $transactions_next->skip($skip+$take)->take($take)->get();

            $result['links']['self'] = $url.((string) ($request->page > 0 ? $request->page : 1));
            if($request->page > 1) $result['links']['prev'] = $url.((string) ($request->page - 1));
            if($transactions_next->count() > 0) $result['links']['next'] = $url.((string) ($request->page ? $request->page + 1 : 2));

            $result['data'] = $transactions;

            $response = [
                'code' => '00',
                'message' => 'Success',
            ];

            $response = $response + $result;

        }catch(\Exception $e){
            $response = [
                'code' => (string) str_pad($e->getCode(), 2, "0", STR_PAD_LEFT),
                'message' => $e->getMessage()
            ];
        }

        return response()->json($response, 200);
    }

    public function detail(Request $request, $id=null){
        try{
            $transaction = Transaction::findorfail($id);
            if($transaction->user_id != $request->user_id) throw new \Exception('Transaction not found',11);
            if($transaction->deleted_at) throw new \Exception('Transaction not found',11);

            $response = [
                'code' => '00',
                'message' => 'Success',
                'data' => [
                    'user_id' => $transaction->user_id,
                    'transaction_at' => $transaction->created_at,
                    'total_amount' => $transaction->total_amount,
                    'paid_amount' => $transaction->paid_amount,
                    'change_amount' => $transaction->change_amount,
                    'payment_method' => $transaction->payment_method,
                    'items' => $transaction->items
                ],
            ];

        }catch(\Exception $e){
            $response = [
                'code' => (string) str_pad($e->getCode(), 2, "0", STR_PAD_LEFT),
                'message' => $e->getMessage()
            ];
        }

        return response()->json($response, 200);
    }

    public function store(Request $request){
        DB::beginTransaction();
        try{
            $transaction = new Transaction();
            $transaction->user_id = $request->user_id;
            $transaction->device_timestamp = Carbon::now();
            $transaction->paid_amount = $request->paid_amount;
            $transaction->payment_method = $request->payment_method;
            $transaction->total_amount = 0;
            $transaction->change_amount = 0;
            $transaction->save();

            $total_amount = 0;
            foreach($request->items as $i){
                $item = new TransactionItem();
                $item->transaction_id = $transaction->id;
                $item->title = $i['title'];
                $item->qty = $i['qty'];
                $item->price = $i['price'];
                $item->save();

                $total_amount += $item->qty * $item->price;
            }

            $transaction->total_amount = $total_amount;
            $transaction->change_amount = $transaction->paid_amount - $total_amount;
            $transaction->save();

            $response = [
                'code' => '00',
                'message' => 'Success',
                'user_id' => $transaction->user_id,
                'transaction_at' => $transaction->created_at,
                'total_amount' => $transaction->total_amount,
                'paid_amount' => $transaction->paid_amount,
                'change_amount' => $transaction->change_amount,
                'payment_method' => $transaction->payment_method,
                'items' => $request->items
            ];

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();

            $response = [
                'code' => (string) str_pad($e->getCode(), 2, "0", STR_PAD_LEFT),
                'message' => $e->getMessage()
            ];
        }

        return response()->json($response, 200);
    }

    public function update(Request $request, $id=null){
        DB::beginTransaction();
        try{
            $transaction = Transaction::findorfail($id);
            if($transaction->user_id != $request->user_id) throw new \Exception('Transaction not found',11);
            if($transaction->deleted_at) throw new \Exception('Transaction not found',11);

            $transaction->device_timestamp = Carbon::now();
            $transaction->paid_amount = $request->paid_amount;
            $transaction->payment_method = $request->payment_method;
            $transaction->total_amount = 0;
            $transaction->change_amount = 0;
            $transaction->save();

            $delete = TransactionItem::query()
                ->where('transaction_id', $id)
                ->update(['deleted_at' => Carbon::now()]);

            $total_amount = 0;
            foreach($request->items as $i){
                $item = new TransactionItem();
                $item->transaction_id = $transaction->id;
                $item->title = $i['title'];
                $item->qty = $i['qty'];
                $item->price = $i['price'];
                $item->save();

                $total_amount += $item->qty * $item->price;
            }

            $transaction->total_amount = $total_amount;
            $transaction->change_amount = $transaction->paid_amount - $total_amount;
            $transaction->save();

            $response = [
                'code' => '00',
                'message' => 'Success',
                'user_id' => $transaction->user_id,
                'transaction_at' => $transaction->created_at,
                'total_amount' => $transaction->total_amount,
                'paid_amount' => $transaction->paid_amount,
                'change_amount' => $transaction->change_amount,
                'payment_method' => $transaction->payment_method,
                'items' => $request->items
            ];

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();

            $response = [
                'code' => (string) str_pad($e->getCode(), 2, "0", STR_PAD_LEFT),
                'message' => $e->getMessage()
            ];
        }

        return response()->json($response, 200);
    }

    public function destroy(Request $request, $id=null){
        DB::beginTransaction();
        try{
            $transaction = Transaction::findorfail($id);
            if($transaction->user_id != $request->user_id) throw new \Exception('Transaction not found',11);
            if($transaction->deleted_at) throw new \Exception('Transaction not found',11);

            $transaction->deleted_at = Carbon::now();
            $transaction->save();

            $delete = TransactionItem::query()
                ->where('transaction_id', $id)
                ->update(['deleted_at' => Carbon::now()]);

            $response = [
                'code' => '00',
                'message' => 'Success'
            ];

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();

            $response = [
                'code' => (string) str_pad($e->getCode(), 2, "0", STR_PAD_LEFT),
                'message' => $e->getMessage()
            ];
        }

        return response()->json($response, 200);
    }
}
