<?php

namespace App\Http\Controllers;

use App\Models\EncryptedFile;
use App\Services\EncryptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function index()
    {
        $files = EncryptedFile::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('files.index', compact('files'));
    }

    public function create()
    {
        return view('files.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'file_type' => 'required|in:identity_card,document,video',
            'encryption_algorithm' => 'required|in:aes,des,rc4'
        ]);

        try {
            // Get file contents
            $file = $request->file('file');
            $fileContent = file_get_contents($file->getRealPath());

            // Encrypt file
            $encryptionResult = $this->encryptionService->encrypt(
                $fileContent,
                $request->encryption_algorithm
            );

            // Generate unique filename
            $storedName = Str::random(40);

            // Store encrypted file
            Storage::put(
                "encrypted/{$storedName}",
                $encryptionResult['data']
            );

            // Save file information
            EncryptedFile::create([
                'user_id' => auth()->id(),
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $storedName,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'encryption_algorithm' => $request->encryption_algorithm,
                'encryption_key' => base64_encode($encryptionResult['key']),
                'encryption_iv' => isset($encryptionResult['iv']) ? base64_encode($encryptionResult['iv']) : null,
                'encryption_time' => $encryptionResult['time'],
                'file_type' => $request->file_type
            ]);

            return redirect()->route('files.index')
                ->with('success', 'File uploaded and encrypted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to upload and encrypt file: ' . $e->getMessage()]);
        }
    }

    public function download(EncryptedFile $file)
    {
        // Verify ownership
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $encryptedContent = Storage::get("encrypted/{$file->stored_name}");

            // Decrypt file
            $decryptionResult = $this->encryptionService->decrypt(
                $encryptedContent,
                $file->encryption_algorithm,
                base64_decode($file->encryption_key),
                $file->encryption_iv ? base64_decode($file->encryption_iv) : null
            );

            // Update decryption time
            $file->update(['decryption_time' => $decryptionResult['time']]);

            return response($decryptionResult['data'])
                ->header('Content-Type', $file->mime_type)
                ->header('Content-Disposition', 'attachment; filename="' . $file->original_name . '"');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to decrypt and download file: ' . $e->getMessage()]);
        }
    }

    public function analysis()
    {
        // Mengambil statistik per algoritma enkripsi
        $analytics = EncryptedFile::where('user_id', auth()->id())
            ->select('encryption_algorithm')
            ->selectRaw('COUNT(*) as total_files')
            ->selectRaw('AVG(file_size) as avg_file_size')
            ->selectRaw('AVG(encryption_time) as avg_encryption_time')
            ->selectRaw('AVG(decryption_time) as avg_decryption_time')
            ->selectRaw('MIN(encryption_time) as min_encryption_time')
            ->selectRaw('MAX(encryption_time) as max_encryption_time')
            ->selectRaw('MIN(file_size) as min_file_size')
            ->selectRaw('MAX(file_size) as max_file_size')
            ->groupBy('encryption_algorithm')
            ->get();

        // Mengambil statistik per tipe file
        $fileTypeStats = EncryptedFile::where('user_id', auth()->id())
            ->select('file_type')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(file_size) as avg_size')
            ->groupBy('file_type')
            ->get();

        // Total statistik
        $totalStats = [
            'total_files' => EncryptedFile::where('user_id', auth()->id())->count(),
            'total_size' => EncryptedFile::where('user_id', auth()->id())->sum('file_size'),
            'avg_encryption_time' => EncryptedFile::where('user_id', auth()->id())->avg('encryption_time'),
        ];

        return view('files.analysis', compact('analytics', 'fileTypeStats', 'totalStats'));
    }
}
