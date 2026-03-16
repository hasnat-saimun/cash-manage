<?php

namespace Tests\Feature;

use App\Http\Controllers\transactionController;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClientTransactionUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function saveTransaction(transactionController $controller, $sessionStore, array $payload)
    {
        $request = Request::create('/save-transaction', 'POST', $payload);
        $request->setLaravelSession($sessionStore);
        app()->instance('request', $request);

        return $controller->saveTransaction($request);
    }

    public function test_updating_transaction_to_another_client_adjusts_both_client_balances(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'tester@example.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);
        $business = Business::create([
            'name' => 'Test Business',
            'slug' => 'test-business',
        ]);

        $business->users()->attach($user->id, ['role' => 'owner']);

        $clientOneId = DB::table('client_creations')->insertGetId([
            'client_name' => 'Client One',
            'client_email' => 'one@example.com',
            'client_phone' => '01700000001',
            'client_regDate' => now()->toDateString(),
            'business_id' => $business->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clientTwoId = DB::table('client_creations')->insertGetId([
            'client_name' => 'Client Two',
            'client_email' => 'two@example.com',
            'client_phone' => '01700000002',
            'client_regDate' => now()->toDateString(),
            'business_id' => $business->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sessionData = [
            'business_id' => $business->id,
            'business_name' => $business->name,
        ];

        $this->be($user);

        $controller = app(transactionController::class);
        $sessionStore = app('session')->driver();
        $sessionStore->start();
        $sessionStore->put($sessionData);

        $createResponse = $this->saveTransaction($controller, $sessionStore, [
            'clientId' => $clientOneId,
            'type' => 'Credit',
            'amount' => 100,
            'date' => '2026-03-16',
            'description' => 'Original transaction',
        ]);

        $this->assertSame('Transaction saved.', $sessionStore->get('success'));
        $this->assertSame(302, $createResponse->getStatusCode());

        $transactionId = DB::table('transactions')
            ->where('business_id', $business->id)
            ->value('id');

        $sessionStore->forget('success');

        $secondClientOneResponse = $this->saveTransaction($controller, $sessionStore, [
            'clientId' => $clientOneId,
            'type' => 'Debit',
            'amount' => 20,
            'date' => '2026-03-17',
            'description' => 'Later client one transaction',
        ]);

        $this->assertSame('Transaction saved.', $sessionStore->get('success'));
        $this->assertSame(302, $secondClientOneResponse->getStatusCode());

        $clientOneLaterTxnId = DB::table('transactions')
            ->where('business_id', $business->id)
            ->where('transaction_client_name', (string) $clientOneId)
            ->where('date', '2026-03-17')
            ->value('id');

        $sessionStore->forget('success');

        $clientTwoExistingResponse = $this->saveTransaction($controller, $sessionStore, [
            'clientId' => $clientTwoId,
            'type' => 'Credit',
            'amount' => 10,
            'date' => '2026-03-17',
            'description' => 'Existing client two transaction',
        ]);

        $this->assertSame('Transaction saved.', $sessionStore->get('success'));
        $this->assertSame(302, $clientTwoExistingResponse->getStatusCode());

        $clientTwoLaterTxnId = DB::table('transactions')
            ->where('business_id', $business->id)
            ->where('transaction_client_name', (string) $clientTwoId)
            ->where('date', '2026-03-17')
            ->value('id');

        $this->assertSame(80.0, (float) DB::table('client_balances')
            ->where('client_id', $clientOneId)
            ->where('business_id', $business->id)
            ->value('balance'));

        $this->assertSame(10.0, (float) DB::table('client_balances')
            ->where('client_id', $clientTwoId)
            ->where('business_id', $business->id)
            ->value('balance'));

        $sessionStore->forget('success');

        $updateResponse = $this->saveTransaction($controller, $sessionStore, [
            'itemId' => $transactionId,
            'clientId' => $clientTwoId,
            'type' => 'Debit',
            'amount' => 50,
            'date' => '2026-03-16',
            'description' => 'Corrected transaction',
        ]);

        $this->assertSame('Transaction saved.', $sessionStore->get('success'));
        $this->assertSame(302, $updateResponse->getStatusCode());

        $this->assertSame(-20.0, (float) DB::table('client_balances')
            ->where('client_id', $clientOneId)
            ->where('business_id', $business->id)
            ->value('balance'));

        $this->assertSame(-40.0, (float) DB::table('client_balances')
            ->where('client_id', $clientTwoId)
            ->where('business_id', $business->id)
            ->value('balance'));

        $updatedTransaction = DB::table('transactions')->where('id', $transactionId)->first();
        $updatedClientOneLaterTxn = DB::table('transactions')->where('id', $clientOneLaterTxnId)->first();
        $updatedClientTwoLaterTxn = DB::table('transactions')->where('id', $clientTwoLaterTxnId)->first();

        $this->assertSame((string) $clientTwoId, $updatedTransaction->transaction_client_name);
        $this->assertSame('Debit', $updatedTransaction->type);
        $this->assertSame(50.0, (float) $updatedTransaction->amount);
        $this->assertSame(-50.0, (float) $updatedTransaction->txnBalance);
        $this->assertSame(-20.0, (float) $updatedClientOneLaterTxn->txnBalance);
        $this->assertSame(-40.0, (float) $updatedClientTwoLaterTxn->txnBalance);
    }
}