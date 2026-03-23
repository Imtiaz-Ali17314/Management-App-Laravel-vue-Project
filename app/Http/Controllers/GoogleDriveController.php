<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Google\Client;
use Google\Service\Drive;
use App\Models\GoogleToken;
use Illuminate\Http\Request;

class GoogleDriveController extends Controller
{
    // ======================
    // Google Client setup
    // ======================
    private function getClient(): Client
    {
        // Google API client initialize karna
        $client = new Client();
        $client->setClientId(config('services.google.client_id')); // Google project ka client_id
        $client->setClientSecret(config('services.google.client_secret')); // client_secret
        $client->setRedirectUri(config('services.google.redirect')); // redirect URL
        $client->addScope(Drive::DRIVE_READONLY); // sirf read permission
        $client->setAccessType('offline'); // refresh token allow karega
        $client->setPrompt('consent'); // har baar consent screen dikhane ke liye
        return $client;
    }

    // ======================
    // Get authorized Google Drive service
    // ======================
    private function getAuthorizedService(): Drive
    {
        // Latest stored token get karna (DB se)
        $row = GoogleToken::latest()->first();
        if (!$row) {
            abort(401, 'No Google token stored');
        }

        $client = $this->getClient();
        $client->setAccessToken($row->access_token);

        // Token expire ho to refresh karna
        if ($client->isAccessTokenExpired()) {
            $refreshToken = $row->refresh_token ?? ($row->access_token['refresh_token'] ?? null);
            if ($refreshToken) {
                // Refresh token se naya access token lana
                $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

                // Agar refresh_token missing ho to purana use karo
                if (!isset($newToken['refresh_token'])) {
                    $newToken['refresh_token'] = $refreshToken;
                }

                // DB me token update karna
                $row->update([
                    'access_token'  => $newToken,
                    'refresh_token' => $newToken['refresh_token'] ?? $refreshToken,
                    'expires_at'    => isset($newToken['expires_in'])
                        ? now()->addSeconds((int)$newToken['expires_in'])
                        : null,
                ]);

                $client->setAccessToken($newToken);
            }
        }

        // Authorized Google Drive service return
        return new Drive($client);
    }

    // ======================
    // Step 1: Google Auth URL par redirect
    // ======================
    public function redirectToGoogle()
    {
        return redirect()->away($this->getClient()->createAuthUrl());
    }

    // ======================
    // Step 2: Google callback handle
    // ======================
    public function handleGoogleCallback(Request $request)
    {
        if (!$request->filled('code')) {
            return response('Missing code', 400);
        }

        $client = $this->getClient();
        $token  = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return response()->json($token, 400);
        }

        $expiresAt = isset($token['expires_in'])
            ? Carbon::now()->addSeconds((int)$token['expires_in'])
            : null;

        // Pehle purana token delete karo (1 user ke liye single token store ho)
        GoogleToken::query()->delete();

        // Naya token save karo DB me
        GoogleToken::create([
            'access_token'  => $token,
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_at'    => $expiresAt,
        ]);

        // Redirect back to frontend
        return redirect('/drive');
    }

    // ======================
    // File data format helper
    // ======================
    private function formatFiles($files)
    {
        return array_map(function ($f) {
            return [
                'id'            => $f->getId(),
                'name'          => $f->getName(),
                'mimeType'      => $f->getMimeType(),
                'isFolder'      => $f->getMimeType() === 'application/vnd.google-apps.folder',
                'webViewLink'   => $f->getWebViewLink(), // preview link
                'iconLink'      => $f->getIconLink(), // file icon
                'thumbnailLink' => $f->getThumbnailLink(), // image/pdf thumbnail
            ];
        }, $files->getFiles() ?? []);
    }

    // ======================
    // My Drive root list
    // ======================
    public function listMyDrive()
    {
        $service = $this->getAuthorizedService();
        $files = $service->files->listFiles([
            'q'       => "'root' in parents and trashed=false", // root ke andar files
            'pageSize'=> 50,
            'fields'  => 'files(id,name,mimeType,webViewLink,iconLink,thumbnailLink)',
        ]);
        return response()->json($this->formatFiles($files));
    }

    // ======================
    // Shared with Me list
    // ======================
    public function listSharedWithMe()
    {
        $service = $this->getAuthorizedService();
        $files = $service->files->listFiles([
            'q'       => "sharedWithMe and trashed=false", // shared with me filter
            'pageSize'=> 50,
            'fields'  => 'files(id,name,mimeType,webViewLink,iconLink,thumbnailLink)',
        ]);
        return response()->json($this->formatFiles($files));
    }

    // ======================
    // Any folder contents list
    // ======================
    public function listFolderContents($id)
    {
        $service = $this->getAuthorizedService();
        $files = $service->files->listFiles([
            'q'       => "'{$id}' in parents and trashed=false", // specific folder ka data
            'pageSize'=> 50,
            'fields'  => 'files(id,name,mimeType,webViewLink,iconLink,thumbnailLink)',
        ]);
        return response()->json($this->formatFiles($files));
    }
}
