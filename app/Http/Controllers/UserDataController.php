<?php

namespace App\Http\Controllers;

use App\Models\UserData;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserDataController extends Controller
{
    private $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'encryption_type' => 'required|in:aes,des,rc4'
        ]);

        $file = $request->file('file');
        $fileContent = file_get_contents($file->getRealPath());

        // Encrypt the file
        $encryptionResult = $this->encryptionService->encrypt(
            $fileContent,
            $request->encryption_type
        );

        // Generate unique filename
        $fileName = Str::random(40);

        // Store encrypted file
        Storage::put(
            "encrypted/{$fileName}",
            $encryptionResult['encrypted']
        );

        // Save file information
        UserData::create([
            'user_id' => auth()->id(),
            'file_name' => $fileName,
            'file_path' => "encrypted/{$fileName}",
            'file_type' => $file->getMimeType(),
            'encryption_type' => $request->encryption_type,
            'encryption_key' => base64_encode($encryptionResult['key']),
            'encryption_iv' => isset($encryptionResult['iv']) ? base64_encode($encryptionResult['iv']) : null,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'processing_time' => $encryptionResult['processing_time']
        ]);

        return response()->json([
            'message' => 'File uploaded and encrypted successfully',
            'processing_time' => $encryptionResult['processing_time']
        ]);
    }

    public function download($id)
    {
        $userData = UserData::findOrFail($id);

        // Verify user ownership
        if ($userData->user_id !== auth()->id()) {
            abort(403);
        }

        $encryptedContent = Storage::get($userData->file_path);

        // Decrypt the file
        $decryptionResult = $this->encryptionService->decrypt(
            $encryptedContent,
            $userData->encryption_type,
            base64_decode($userData->encryption_key),
            $userData->encryption_iv ? base64_decode($userData->encryption_iv) : null
        );

        return response($decryptionResult['decrypted'])
            ->header('Content-Type', $userData->file_type)
            ->header('Content-Disposition', 'attachment; filename="' . $userData->original_name . '"');
    }

    public function getAnalytics()
    {
        $analytics = UserData::select('encryption_type')
            ->selectRaw('AVG(processing_time) as avg_processing_time')
            ->selectRaw('COUNT(*) as total_files')
            ->selectRaw('AVG(file_size) as avg_file_size')
            ->groupBy('encryption_type')
            ->get();

        return response()->json($analytics);
    }
}
