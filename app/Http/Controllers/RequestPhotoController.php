<?php

namespace App\Http\Controllers;

use App\Models\RequestPhoto;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves a request photo from the private disk to atelier accounts only (KTD7).
 */
class RequestPhotoController extends Controller
{
    public function __invoke(Request $request, RequestPhoto $photo): StreamedResponse
    {
        $user = $request->user();
        // Outside the panel, so its MFA set-up middleware does not apply: require the enrolled e-mail code here.
        abort_unless($user?->canAccessPanel(Filament::getPanel('atelier')) && $user->hasEmailAuthentication(), 403);
        abort_unless(Storage::disk(RequestPhoto::DISK)->exists($photo->path), 404);

        return Storage::disk(RequestPhoto::DISK)->response($photo->path, headers: ['Cache-Control' => 'private, no-store']);
    }
}
