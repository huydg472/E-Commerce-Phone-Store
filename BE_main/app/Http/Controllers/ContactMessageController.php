<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactMessageController extends Controller
{
    private function subjectLabel(string $subject): string
    {
        return match ($subject) {
            'product_advice' => 'Tu van san pham',
            'order_support' => 'Ho tro don hang',
            'warranty' => 'Bao hanh san pham',
            'feedback' => 'Gop y dich vu',
            default => 'Lien he',
        };
    }

    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $settings = SiteSetting::current();
        $recipientEmail = trim((string)($settings->contact_email ?: $settings->support_email));

        $contactMessage = ContactMessage::create([
            ...$request->validated(),
            'recipient_email' => $recipientEmail ?: null,
            'delivery_status' => 'pending',
        ]);

        $deliveryMessage = 'Yeu cau da duoc tiep nhan.';

        if ($recipientEmail !== '') {
            try {
                $body = implode(PHP_EOL, [
                    'Ban co mot yeu cau lien he moi:',
                    '',
                    'Ho va ten: ' . $contactMessage->name,
                    'Email: ' . $contactMessage->email,
                    'So dien thoai: ' . $contactMessage->phone,
                    'Chu de: ' . $this->subjectLabel($contactMessage->subject),
                    '',
                    'Noi dung:',
                    $contactMessage->message,
                ]);

                Mail::raw($body, function ($message) use ($recipientEmail, $settings, $contactMessage) {
                    $message->to($recipientEmail)
                        ->subject(sprintf('[%s] Lien he moi: %s', $settings->brand_name ?: $settings->site_name, $this->subjectLabel($contactMessage->subject)));

                    $fromAddress = trim((string)($settings->support_email ?: config('mail.from.address')));

                    if ($fromAddress !== '') {
                        $message->from($fromAddress, $settings->brand_name ?: $settings->site_name);
                    }

                    if ($contactMessage->email !== '') {
                        $message->replyTo($contactMessage->email, $contactMessage->name);
                    }
                });

                $contactMessage->update([
                    'delivery_status' => 'delivered',
                    'delivered_at' => now(),
                ]);
            } catch (Throwable $throwable) {
                Log::warning('Contact message delivery failed.', [
                    'contact_message_id' => $contactMessage->id,
                    'recipient_email' => $recipientEmail,
                    'error' => $throwable->getMessage(),
                ]);

                $contactMessage->update([
                    'delivery_status' => 'failed',
                ]);

                $deliveryMessage = 'Yeu cau da duoc luu, nhung he thong gui email tam thoi chua thanh cong.';
            }
        }

        return response()->json([
            'success' => true,
            'message' => $deliveryMessage,
            'data' => $contactMessage->fresh(),
        ], 201);
    }
}
