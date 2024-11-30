<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use TCPDF;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA\PrivateKey;
use phpseclib3\Crypt\RSA\PublicKey as RSAPublicKey;
use App\Models\UserKey;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DigitalSignatureController extends Controller
{
    public function index()
    {
        // Cek apakah user sudah memiliki key pair
        $hasKeyPair = UserKey::where('user_id', Auth::id())->exists();

        // Return view dengan data hasKeyPair
        return view('digital-signature.index', [
            'hasKeyPair' => $hasKeyPair
        ]);
    }

    public function generateKeyPair()
    {
        try {
            $private = RSA::createKey(2048);
            $public = $private->getPublicKey();

            $userKey = UserKey::firstOrNew(['user_id' => Auth::id()]);
            $userKey->private_key = $private->toString('PKCS8');
            $userKey->public_key = $public->toString();
            $userKey->save();

            return redirect()->back()->with('success', 'Key pair berhasil dibuat');
        } catch (\Exception $e) {
            Log::error('Key generation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat key pair');
        }
    }

    public function signPDF(Request $request)
    {
        $request->validate([
            'pdf_file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10000'
            ]
        ]);

        try {
            Log::info('Starting PDF signing process');

            // Get user's key pair
            $userKey = UserKey::where('user_id', Auth::id())->firstOrFail();
            $privateKey = PublicKeyLoader::load($userKey->private_key);

            if (!($privateKey instanceof PrivateKey)) {
                throw new \Exception('Format private key tidak valid');
            }

            // Read original PDF content
            $originalContent = file_get_contents($request->file('pdf_file')->getPathname());

            // Store original content
            $timestamp = time();
            $baseFilename = 'signed_' . $timestamp;
            $originalPath = storage_path('app/public/signatures/' . $baseFilename . '_original');
            file_put_contents($originalPath, $originalContent);

            // Create hash from original content
            $hash = hash('sha256', $originalContent);

            // Create signature
            $signature = $privateKey->sign($hash);

            // Prepare signature data
            $signatureData = [
                'signedBy' => Auth::user()->name,
                'date' => now()->toString(),
                'hash' => $hash,
                'signature' => base64_encode($signature),
                'publicKey' => $userKey->public_key
            ];

            // Create signed PDF
            $pdf = new TCPDF();
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor(Auth::user()->name);
            $pdf->SetTitle('Signed Document');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Add original content
            $pdf->AddPage();
            $pdf->writeHTML($originalContent, true, false, true, false, '');

            // Add signature page
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Digital Signature Information', 0, 1, 'C');
            $pdf->Ln(10);

            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Document signed by: ' . Auth::user()->name, 0, 1, 'L');
            $pdf->Cell(0, 10, 'Date: ' . now()->toString(), 0, 1, 'L');

            Storage::makeDirectory('public/signatures');

            // Save files
            $pdfPath = storage_path('app/public/signatures/' . $baseFilename . '.pdf');
            $sigPath = storage_path('app/public/signatures/' . $baseFilename . '.sig');

            $pdf->Output($pdfPath, 'F');
            file_put_contents($sigPath, json_encode($signatureData));

            Log::info('Files saved', [
                'pdf' => $pdfPath,
                'sig' => $sigPath,
                'original' => $originalPath
            ]);

            return response()->download($pdfPath)->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('PDF Signing Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Gagal menandatangani dokumen: ' . $e->getMessage());
        }
    }

    public function verifySignature(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10000'
        ]);

        try {
            $filename = $request->file('pdf_file')->getClientOriginalName();
            Log::info('Verifying PDF:', [
                'filename' => $filename,
                'size' => $request->file('pdf_file')->getSize()
            ]);

            if (preg_match('/signed_(\d+)\.pdf$/', $filename, $matches)) {
                $baseFilename = 'signed_' . $matches[1];
                $sigPath = storage_path('app/public/signatures/' . $baseFilename . '.sig');
                $originalPath = storage_path('app/public/signatures/' . $baseFilename . '_original');

                if (!file_exists($sigPath) || !file_exists($originalPath)) {
                    return redirect()->back()
                        ->with('error', 'File signature atau konten asli tidak ditemukan');
                }

                // Load signature data
                $signatureData = json_decode(file_get_contents($sigPath), true);
                if (!$signatureData) {
                    return redirect()->back()
                        ->with('error', 'Format tanda tangan tidak valid');
                }

                // Get original content
                $originalContent = file_get_contents($originalPath);

                // Calculate hash from original content
                $currentHash = hash('sha256', $originalContent);

                Log::info('Hash comparison:', [
                    'current' => $currentHash,
                    'stored' => $signatureData['hash']
                ]);

                // Verify hash
                if ($currentHash !== $signatureData['hash']) {
                    return redirect()->back()
                        ->with('error', 'Dokumen telah dimodifikasi setelah ditandatangani');
                }

                // Verify signature
                $publicKey = PublicKeyLoader::load($signatureData['publicKey']);
                if (!$publicKey instanceof RSAPublicKey) {
                    throw new \Exception('Format public key tidak valid');
                }

                $signature = base64_decode($signatureData['signature']);
                if ($publicKey->verify($signatureData['hash'], $signature)) {
                    return redirect()->back()
                        ->with('success', 'Tanda tangan valid. Dokumen ditandatangani oleh ' .
                            $signatureData['signedBy'] . ' pada ' . $signatureData['date']);
                }

                return redirect()->back()
                    ->with('error', 'Tanda tangan digital tidak valid');
            }

            return redirect()->back()
                ->with('error', 'Format nama file tidak valid. Harap gunakan file yang ditandatangani oleh sistem.');

        } catch (\Exception $e) {
            Log::error('Verification Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Gagal memverifikasi tanda tangan: ' . $e->getMessage());
        }
    }
}
