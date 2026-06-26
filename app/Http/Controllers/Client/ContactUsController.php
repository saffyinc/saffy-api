<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Models\ContactForm;

class ContactUsController extends Controller
{
    public function submitContactForm(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        ContactForm::create($data);

        $html = View::make('emails.SendNotification', [
            'name' => $data['name'],
            'email' => $data['email'],
            'contactMessage' => $data['message'],
            'mailSubject' => $data['subject'] ?? null,
        ])->render();

        $response = Http::withToken(env('RESEND_API_KEY'))
            ->post('https://api.resend.com/emails', [
                'from' => 'Saffy Inc. <onboarding@resend.dev>',
                'to' => [env('CONTACT_NOTIFICATION_EMAIL')],
                'subject' => $data['subject'] ?? 'New Contact Form Message',
                'html' => $html,
            ]);

        if (!$response->successful()) {
            Log::error('Resend email failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'message' => 'Contact form saved, but email notification failed.',
            ], 500);
        }

        return response()->json([
            'message' => 'Email sent successfully',
        ]);
    }
}
