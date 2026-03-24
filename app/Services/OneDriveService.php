<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\OneDrive;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class OneDriveService
{
    /** token record lao */
    public function getToken($userId)
    {
        return OneDrive::where('user_id', $userId)->first();
    }

    /** path normalize (Graph ka /drive/root: hatao) */
    private function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '/') return '/';
        if (str_starts_with($path, '/drive/root:')) {
            $path = substr($path, strlen('/drive/root:'));
            if ($path === false) $path = '/';
        }
        if ($path !== '/' && !str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        return $path;
    }

    /** ✅ getAccessToken (for backward compatibility) */
    public function getAccessToken($userId)
    {
        return $this->refreshAccessToken($userId);
    }

    /** token refresh ya return */
    public function refreshAccessToken($userId)
    {
        $token = $this->getToken($userId);
        if (!$token) {
            throw new \Exception('No OneDrive token found for this user.');
        }

        // Agar abhi valid hai to wahi return karo
        if ($token->expires_at && Carbon::now()->lt($token->expires_at)) {
            return $token->access_token;
        }

        // Refresh token
        $response = Http::asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'client_id'     => config('services.onedrive.client_id'),
            'client_secret' => config('services.onedrive.client_secret'),
            'refresh_token' => $token->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to refresh OneDrive access token: ' . $response->body());
        }

        $data = $response->json();

        if (!isset($data['access_token'])) {
            throw new \Exception('Unable to refresh OneDrive token, response: ' . json_encode($data));
        }

        $token->update([
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $token->refresh_token,
            'expires_at'    => Carbon::now()->addSeconds($data['expires_in']),
        ]);

        return $data['access_token'];
    }

    /** list files/folders */
    public function listFiles($userId, $path = '/')
    {
        $accessToken = $this->refreshAccessToken($userId);
        $path = $this->normalizePath($path);

        $url = $path === '/'
            ? "https://graph.microsoft.com/v1.0/me/drive/root/children"
            : "https://graph.microsoft.com/v1.0/me/drive/root:" . rawurlencode($path) . ":/children";

        $response = Http::withToken($accessToken)->get($url);
        if ($response->failed()) {
            throw new \Exception('Failed to fetch OneDrive files: ' . $response->body());
        }

        return $response->json();
    }

    /** create folder */
    public function createFolder($userId, $folderName, $parentPath = '/')
    {
        $accessToken = $this->refreshAccessToken($userId);
        $parentPath = $this->normalizePath($parentPath);

        $url = $parentPath === '/'
            ? "https://graph.microsoft.com/v1.0/me/drive/root/children"
            : "https://graph.microsoft.com/v1.0/me/drive/root:" . rawurlencode($parentPath) . ":/children";

        $response = Http::withToken($accessToken)->post($url, [
            'name' => $folderName,
            'folder' => new \stdClass(),
            '@microsoft.graph.conflictBehavior' => 'rename'
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to create OneDrive folder: ' . $response->body());
        }

        return $response->json();
    }

    /** upload file */

    public function uploadFile($userId, $localFile, $path, $fileName)
    {
        $token = $this->getAccessToken($userId);
    
        // ✅ Final path OneDrive ke andar
        $uploadUrl = "https://graph.microsoft.com/v1.0/me/drive/root:" 
                   . rtrim($path, '/') . "/" . $fileName . ":/content";
    
        $client = new \GuzzleHttp\Client();
    
        $response = $client->put($uploadUrl, [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
            // ✅ Binary stream send karo, json_encode mat karo
            'body' => fopen($localFile, 'r'),
        ]);
    
        return json_decode($response->getBody(), true);
    }


    /** download file */
    public function downloadFile($userId, $fileId, $savePath = null)
    {
        $accessToken = $this->refreshAccessToken($userId);
        $url = "https://graph.microsoft.com/v1.0/me/drive/items/{$fileId}/content";

        // Direct response instead of saving to storage
        $response = Http::withToken($accessToken)->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to download OneDrive file: ' . $response->body());
        }

        // Agar savePath diya gaya hai to storage me save karo
        if ($savePath) {
            Storage::put($savePath, $response->body());
            return $savePath;
        }

        // Otherwise direct content return kar do (controller se browser me stream ho jayega)
        return $response->body();
    }

    /** delete item */ 
    public function deleteItem($userId, $itemId)
    {
        $accessToken = $this->refreshAccessToken($userId);
        $url = "https://graph.microsoft.com/v1.0/me/drive/items/{$itemId}";

        $response = Http::withToken($accessToken)->delete($url);
        if ($response->failed()) {
            throw new \Exception('Failed to delete OneDrive item: ' . $response->body());
        }

        return ['message' => 'Item deleted successfully'];
    }
}
