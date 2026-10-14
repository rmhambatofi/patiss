<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessage;
use App\Mail\ContactMessageForArtisan;
use App\Models\ContactMessage;
use App\Settings\ShopSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

/**
 * Formulaire « Écrivez-moi » de la vitrine : le message est gardé pour l'atelier et
 * transmis par e-mail à l'artisan.
 */
class ContactMessageController extends Controller
{
    public function store(StoreContactMessage $request, ShopSettings $settings): RedirectResponse
    {
        $message = ContactMessage::create($request->validated());

        if ($settings->notification_email) {
            Mail::to($settings->notification_email)->send(new ContactMessageForArtisan($message));
        }

        return redirect()->to(route('home').'#contact')
            ->with('contact_sent', $message->first_name);
    }
}
