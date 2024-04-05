<?php


namespace Tests\Feature;

use App\Models\Wallet;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class WalletFeatureTest extends TestCase
{

    /** @test */
    public function test_balance_requires_pin_code()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/wallet/balance', []);

        $response->assertStatus(400)
            ->assertJsonValidationErrors('pin_code');
    }

    /** @test  */
    public function test_balance_with_invalid_pin_code()
    {
        $pinCode = '123456';
        $invalidPinCode = '456789';
        $user = User::factory()->create();

        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->pin_code = Hash::make($pinCode);
        $wallet->save();

        $response = $this->actingAs($user)
            ->postJson('/api/wallet/balance', ['pin_code' => $invalidPinCode]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid PIN code']);
    }

    public function test_balance_with_valid_pin_code()
    {
        $user = User::factory()->create();
        $pinCode = '123456';

        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->pin_code = Hash::make($pinCode);
        $wallet->save();

        $response = $this->actingAs($user)
            ->post('/api/wallet/balance', ['pin_code' => $pinCode]);

        $response->assertStatus(200)
            ->assertJsonStructure(['balance']);
    }

    /** @test */
    public function authenticated_user_can_deposit_funds()
    {
        $user = User::factory()->create();
        $pinCode = '123456';

        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->pin_code = Hash::make($pinCode);
        $wallet->save();
        $response = $this->actingAs($user)->post('/api/wallet/deposit', ['amount' => 50]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Deposit successful']);
    }

    /** @test */
    public function authenticated_user_can_transfer_funds()
    {
        $pinCode = '123456';
        $sender = User::factory()->create();
        $senderWallet = new Wallet();
        $senderWallet->user_id = $sender->id;
        $senderWallet->balance = 100;
        $senderWallet->pin_code = Hash::make($pinCode);
        $senderWallet->save();

        $recipient = User::factory()->create();
        $recipientWallet = new Wallet();
        $recipientWallet->user_id = $recipient->id;
        $recipientWallet->pin_code = Hash::make($pinCode);
        $recipientWallet->save();


        $response = $this->actingAs($sender)->post('/api/wallet/transfer', [
            'recipient_email' => $recipient->email,
            'amount' => 50,
            'pin_code' => $pinCode
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Transfer successful']);
    }

    /** @test */
    public function authenticated_user_cannot_transfer_more_than_balance()
    {
        $pinCode = '123456';
        $sender = User::factory()->create();
        $senderWallet = new Wallet();
        $senderWallet->user_id = $sender->id;
        $senderWallet->balance = 100;
        $senderWallet->pin_code = Hash::make($pinCode);
        $senderWallet->save();

        $recipient = User::factory()->create();
        $recipientWallet = new Wallet();
        $recipientWallet->user_id = $recipient->id;
        $recipientWallet->pin_code = Hash::make($pinCode);
        $recipientWallet->save();

        $response = $this->actingAs($sender)->post('/api/wallet/transfer', [
            'recipient_email' => $recipient->email,
            'amount' => 150,
            'pin_code' => $pinCode
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Insufficient balance']);

        $this->assertEquals(100, $sender->wallet->fresh()->balance);
        $this->assertEquals(0, $recipient->wallet->fresh()->balance);
    }

    /** @test */
    public function authenticated_user_cannot_transfer_with_invalid_pin()
    {
        $correctPinCode = '123456';
        $invalidPinCode = '654321';

        $sender = User::factory()->create();
        $senderWallet = new Wallet();
        $senderWallet->user_id = $sender->id;
        $senderWallet->balance = 100;
        $senderWallet->pin_code = Hash::make($correctPinCode);
        $senderWallet->save();

        $recipient = User::factory()->create();
        $recipientWallet = new Wallet();
        $recipientWallet->user_id = $recipient->id;
        $recipientWallet->pin_code = Hash::make($correctPinCode);
        $recipientWallet->save();

        $response = $this->actingAs($sender)->post('/api/wallet/transfer', [
            'recipient_email' => $recipient->email,
            'amount' => 50,
            'pin_code' => $invalidPinCode // Incorrect PIN code
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid PIN code']);

        $this->assertEquals(100, $sender->wallet->fresh()->balance);
        $this->assertEquals(0, $recipient->wallet->fresh()->balance);
    }

    /** @test */
    public function recipient_wallet_not_found_error_is_returned_when_transferring_funds()
    {
        $sender = User::factory()->create();

        $recipient = User::factory()->create();

        $senderWallet = new Wallet();
        $senderWallet->user_id = $sender->id;
        $senderWallet->balance = 100;
        $senderWallet->pin_code = Hash::make('123456');
        $senderWallet->save();

        $response = $this->actingAs($sender)->post('/api/wallet/transfer', [
            'recipient_email' => $recipient->email,
            'amount' => 50,
            'pin_code' => '123456'
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Recipient wallet not found']);

        $this->assertEquals(100, $sender->wallet->fresh()->balance);
    }

    /** @test */
    public function test_change_pin_code_successfully()
    {
        $user = User::factory()->create();

        $pinCode = '123456';
        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->pin_code = Hash::make($pinCode);
        $wallet->save();

        $this->actingAs($user);

        $requestData = [
            'old_pin_code' => '123456',
            'new_pin_code' => '654321',
            'confirm_new_pin_code' => '654321',
            'password' => 'password',
        ];

        $response = $this->postJson('/api/wallet/change-pin', $requestData);
        $response->assertStatus(200)
            ->assertJson(['message' => 'PIN code changed successfully']);

        $this->assertTrue(Hash::check('654321', $user->wallet->fresh()->pin_code));
    }

    /** @test */
    public function test_change_pin_code_with_invalid_password()
    {
        $user = User::factory()->create();

        $pinCode = '123456';
        $wallet = new Wallet();
        $wallet->user_id = $user->id;
        $wallet->pin_code = Hash::make($pinCode);
        $wallet->save();


        $this->actingAs($user);

        $requestData = [
            'old_pin_code' => '123456',
            'new_pin_code' => '654321',
            'confirm_new_pin_code' => '654321',
            'password' => 'invalidpassword',
        ];

        $response = $this->postJson('/api/wallet/change-pin', $requestData);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid password']);

        $this->assertTrue(Hash::check('123456', $user->wallet->fresh()->pin_code));
    }
}
