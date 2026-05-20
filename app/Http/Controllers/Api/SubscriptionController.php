<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    public function requestUpgrade(Request $request)
    {
        $request->validate([
            'tier' => 'required|string|in:plus,pro',
            'payment_proof_url' => 'required|url'
        ]);

        $user = $request->user();
        $targetTier = strtoupper($request->tier);
        $currentTier = strtoupper($user->tier->value);

        if ($currentTier === $targetTier) {
            return $this->errorResponse("You are already on the {$targetTier} tier.", 400);
        }

        $adminWaNumbersStr = env('ADMIN_WA_NUMBERS'); 
        
        if (!$adminWaNumbersStr) {
            Log::error('ADMIN_WA_NUMBERS is not set in .env');
            return $this->errorResponse('System configuration error. Please contact support.', 500);
        }

        $adminNumbers = explode(',', $adminWaNumbersStr);

        $message = "*REQUEST UPGRADE TIER TITIP.IN*\n\n";
        $message .= "ID User: {$user->id}\n";
        $message .= "Nama: {$user->name}\n";
        $message .= "Email: {$user->email}\n";
        $message .= "No WA User: {$user->wa_number}\n";
        $message .= "Tier Saat: *{$currentTier}*\n";
        $message .= "Request: *{$targetTier}*\n\n";
        $message .= "Bukti Bayar: {$request->payment_proof_url}\n";

        $successCount = 0;

        foreach ($adminNumbers as $number) {
            $cleanNumber = trim($number);
            if (!empty($cleanNumber)) {
                $isSent = $this->waService->sendMessage($cleanNumber, $message);
                if ($isSent) {
                    $successCount++;
                }
            }
        }

        if ($successCount === 0) {
            return $this->errorResponse('Failed to send request to Admins via WhatsApp. Please try again later.', 500);
        }

        Log::info("Upgrade request to {$targetTier} by {$user->email} sent to {$successCount} Admin(s).");
        return $this->successResponse(null, 'Upgrade request sent successfully. Admin will process it shortly.');
    }
}