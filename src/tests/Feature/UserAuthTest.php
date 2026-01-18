<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 測試：登入成功應回傳 Token
     */
    public function test_login_successfully_returns_token(): void
    {
        // 1. 建立一個測試使用者
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. 發送登入請求
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'test-device',
        ]);

        // 3. 驗證結果
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ])
            ->assertJson([
                'success' => true,
                'user' => [
                    'email' => 'test@example.com',
                ],
            ]);
    }

    /**
     * 測試：密碼錯誤應回傳 422 驗證錯誤
     */
    public function test_login_fails_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * 測試：無效的 Email 格式應觸發驗證錯誤
     */
    public function test_login_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
            'device_name' => 'test-device',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * 測試：漏填欄位應觸發驗證錯誤
     */
    public function test_login_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    }
}
