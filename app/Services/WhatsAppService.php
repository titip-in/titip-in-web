<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $instanceName;

    public function __construct()
    {
        $this->apiUrl = env('EVOLUTION_API_URL');
        $this->apiKey = env('EVOLUTION_API_KEY');
        $this->instanceName = env('EVOLUTION_INSTANCE_NAME');
    }

    public function sendOTP(string $waNumber, string $otpCode): bool
    {
        $endpoint = "{$this->apiUrl}/message/sendText/{$this->instanceName}";
        
        $message = "Halo,\n\nBerikut adalah kode OTP untuk verifikasi nomor WhatsApp Anda di Titipin.me:\n\n*{$otpCode}*\n\nKode ini hanya berlaku selama 5 menit. Demi keamanan akun Anda, mohon untuk tidak membagikan kode ini kepada siapa pun.";

        return $this->executeRequest($endpoint, $waNumber, $message);
    }

    public function sendMessage(string $waNumber, string $message): bool
    {
        $endpoint = "{$this->apiUrl}/message/sendText/{$this->instanceName}";
        return $this->executeRequest($endpoint, $waNumber, $message);
    }

    private function executeRequest(string $endpoint, string $waNumber, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($endpoint, [
                'number' => $waNumber,
                'text' => $message
            ]);

            if ($response->failed()) {
                Log::error('Evolution API Error: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Evolution API Exception: ' . $e->getMessage());
            return false;
        }
    }
}