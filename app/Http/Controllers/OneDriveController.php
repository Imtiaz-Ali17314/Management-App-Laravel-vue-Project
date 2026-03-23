<?php

namespace App\Http\Controllers;

use App\Models\OneDrive;
use Illuminate\Http\Request;
use App\Services\OneDriveService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class OneDriveController extends Controller
{
    protected $oneDrive;

    public function __construct(OneDriveService $oneDrive)
    {
        $this->oneDrive = $oneDrive;
    }

    /** Testing/Fallback user */
    private function getUserId(): int
    {
        $rec = OneDrive::first();
        return $rec ? $rec->user_id : 1;
    }

    /** OAuth callback: tokens save */
    public function callback(Request $request)
    {
        if (!$request->has('code')) {
            return response()->json(['error' => 'Authorization code missing'], 400);
        }

        $code = $request->query('code');

        $response = Http::asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'client_id'     => config('services.onedrive.client_id'),
            'client_secret' => config('services.onedrive.client_secret'),
            'code'          => $code,
            'redirect_uri'  => config('services.onedrive.redirect'),
            'grant_type'    => 'authorization_code',
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Token exchange failed', 'detail' => $response->json()], 500);
        }

        $data = $response->json();
        $userId = 1; // abhi fixed, baad me Auth::id() use kar sakte hain

        OneDrive::updateOrCreate(
            ['user_id' => $userId],
            [
                'access_token'  => $data['access_token'] ?? null,
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at'    => now()->addSeconds($data['expires_in'] ?? 3500),
            ]
        );

        return response()->json(['message' => 'OneDrive connected successfully']);
    }

    /** List Files/Folders */
    public function listFiles(Request $request)
    {
        try {
            $path = $request->get('path', '/');
            $userId = $this->getUserId();
            $files = $this->oneDrive->listFiles($userId, $path);
            return response()->json($files);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Create Folder */
    public function createFolder(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'path' => 'nullable|string'
            ]);
            $userId = $this->getUserId();
            $folder = $this->oneDrive->createFolder($userId, $request->name, $request->path ?? '/');
            return response()->json($folder);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
       /** Upload File */
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file',
                'path' => 'nullable|string'
            ]);
        
            $userId = $this->getUserId();
        
            // ✅ Original filename preserve karo
            $file = $request->file('file');
            $localFile = $file->getRealPath();
            $fileName = $file->getClientOriginalName();
        
            // ✅ Path normalize (agar empty ho to "/")
            $path = $request->path ?? '/';
        
            // ✅ Call OneDrive service
            $upload = $this->oneDrive->uploadFile($userId, $localFile, $path, $fileName);
        
            return response()->json([
                'success' => true,
                'data' => $upload
            ]);
        } catch (\Throwable $e) {
            Log::error("OneDrive Upload Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    /** ✅ Download File - direct browser download */
    public function downloadFile($itemId)
    {
        try {
            $userId = $this->getUserId();
            $accessToken = $this->oneDrive->getAccessToken($userId);

            $client = new \GuzzleHttp\Client();
            $url = "https://graph.microsoft.com/v1.0/me/drive/items/{$itemId}/content";

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}"
                ],
                'stream' => true,
                'allow_redirects' => true,
            ]);

            $disposition = $response->getHeader('Content-Disposition');
            if (!empty($disposition)) {
                preg_match('/filename="?([^"]+)"?/', $disposition[0], $matches);
                $filename = $matches[1] ?? "{$itemId}.bin";
            } else {
                $filename = "{$itemId}.bin";
            }

            return response($response->getBody()->getContents(), 200)
                ->header('Content-Type', $response->getHeader('Content-Type')[0] ?? 'application/octet-stream')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** ✅ Preview File (inline view e.g. images/pdf) */
    public function previewFile($itemId)
    {
        try {
            $userId = $this->getUserId();
            $accessToken = $this->oneDrive->getAccessToken($userId);

            $client = new \GuzzleHttp\Client();
            $url = "https://graph.microsoft.com/v1.0/me/drive/items/{$itemId}/content";

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}"
                ],
                'stream' => true,
                'allow_redirects' => true,
            ]);

            return response($response->getBody()->getContents(), 200)
                ->header('Content-Type', $response->getHeader('Content-Type')[0] ?? 'application/octet-stream')
                ->header('Content-Disposition', "inline; filename=\"{$itemId}\"");

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** ✅ View File (for showing images as icon thumbnail) */
    public function viewFile($itemId)
    {
        try {
            $userId = $this->getUserId();
            $accessToken = $this->oneDrive->getAccessToken($userId);

            $client = new \GuzzleHttp\Client();
            $url = "https://graph.microsoft.com/v1.0/me/drive/items/{$itemId}/thumbnails/0/medium/content";

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}"
                ],
                'stream' => true,
                'allow_redirects' => true,
            ]);

            return response($response->getBody()->getContents(), 200)
                ->header('Content-Type', $response->getHeader('Content-Type')[0] ?? 'image/jpeg')
                ->header('Content-Disposition', "inline; filename=\"{$itemId}.jpg\"");

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /** Delete File/Folder */
    public function deleteItem($itemId)
    {
        try {
            $userId = $this->getUserId();
            $result = $this->oneDrive->deleteItem($userId, $itemId);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
