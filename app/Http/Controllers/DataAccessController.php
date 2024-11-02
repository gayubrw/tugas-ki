<?php

namespace App\Http\Controllers;

use App\Models\DataAccessRequest;
use App\Models\EncryptedFile;
use App\Models\SharedFile;
use App\Models\User;
use App\Models\UserKey;
use App\Services\KeyManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DataAccessController extends Controller
{
    protected $keyService;

    public function __construct(KeyManagementService $keyService)
    {
        $this->keyService = $keyService;
    }

    public function index()
    {
        $sentRequests = DataAccessRequest::where('requester_id', auth()->id())->with(['owner'])->get();
        $receivedRequests = DataAccessRequest::where('owner_id', auth()->id())->with(['requester'])->get();

        return view('data-access.index', compact('sentRequests', 'receivedRequests'));
    }

    /**
     * Tampilkan form request akses
     */
    public function create($userId)
    {
        $owner = User::findOrFail($userId);
        return view('data-access.create', compact('owner'));
    }

    public function users()
    {
        $users = User::all();
        return view('data-access.users', compact('users'));
    }

    /**
     * Submit request akses baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'owner_id' => 'required|exists:users,id',
            'message' => 'required|string|max:500'
        ]);

        // Buat request baru
        DataAccessRequest::create([
            'requester_id' => auth()->id(),
            'owner_id' => $request->owner_id,
            'message' => $request->message,
            'status' => 'pending'
        ]);

        return redirect()->route('data-access.index')
            ->with('success', 'Access request has been sent.');
    }

    /**
     * Approve request dan generate symmetric key
     */
    public function show(DataAccessRequest $request)
    {
        if ($request->owner_id !== auth()->id()) {
            abort(403);
        }

        // Ambil file yang dimiliki user yang login
        $files = EncryptedFile::where('user_id', auth()->id())->get();

        return view('data-access.show', compact('request', 'files'));
    }

    public function approve(Request $request, DataAccessRequest $dataRequest)
    {
        DB::beginTransaction();
        try {
            Log::info('Approve method started', [
                'action' => $request->action,
                'files' => $request->input('files', []),
                'request_id' => $dataRequest->id
            ]);

            // Validate ownership
            if ($dataRequest->owner_id !== auth()->id()) {
                throw new \Exception('Unauthorized access');
            }

            // Handle rejection
            if ($request->action === 'reject') {
                $dataRequest->update([
                    'status' => 'rejected',
                    'approved_at' => now()
                ]);

                DB::commit();
                return redirect()->route('data-access.index')
                    ->with('success', 'Request has been rejected.');
            }

            // Handle approval
            if ($request->action === 'approve') {
                // Validate files using Laravel's validator
                $validatedData = $request->validate([
                    'files' => 'required|array|min:1',
                    'files.*' => 'required|exists:encrypted_files,id'
                ], [
                    'files.required' => 'Please select at least one file to share.',
                    'files.array' => 'Invalid file selection format.',
                    'files.min' => 'Please select at least one file to share.',
                    'files.*.exists' => 'One or more selected files are invalid.'
                ]);

                Log::info('Files validation passed', [
                    'files_count' => count($validatedData['files']),
                    'files' => $validatedData['files']
                ]);

                // Generate and encrypt key
                $symmetricKey = $this->keyService->generateSymmetricKey();
                $requesterKey = UserKey::where('user_id', $dataRequest->requester_id)->firstOrFail();
                $encryptedKey = $this->keyService->encryptWithPublicKey(
                    $symmetricKey,
                    $requesterKey->public_key
                );
                $encryptedKeyBase64 = base64_encode($encryptedKey);

                // Update request status
                $dataRequest->update([
                    'status' => 'approved',
                    'encrypted_key' => $encryptedKeyBase64,
                    'approved_at' => now(),
                    'expires_at' => now()->addDays(7)
                ]);

                // Remove existing shared files
                SharedFile::where('request_id', $dataRequest->id)->delete();

                // Share new files
                foreach ($validatedData['files'] as $fileId) {
                    SharedFile::create([
                        'request_id' => $dataRequest->id,
                        'encrypted_file_id' => $fileId
                    ]);

                    Log::info('File shared', [
                        'request_id' => $dataRequest->id,
                        'encrypted_file_id' => $fileId
                    ]);
                }

                DB::commit();
                return redirect()->route('data-access.index')
                    ->with('success', 'Request approved and files shared successfully.');
            }

            throw new \Exception('Invalid action specified');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error', [
                'errors' => $e->errors()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in approve method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to process request: ' . $e->getMessage()]);
        }
    }

    public function viewSharedFiles(DataAccessRequest $request)
    {
        try {
            if ($request->requester_id !== auth()->id()) {
                abort(403);
            }

            Log::info('Loading shared files', [
                'request_id' => $request->id,
                'requester_id' => auth()->id()
            ]);

            // Load shared files with their encrypted files
            $files = SharedFile::with(['encryptedFile'])
                ->where('request_id', $request->id)
                ->get();

            Log::info('Shared files loaded', [
                'files_count' => $files->count(),
                'files' => $files->map(function($file) {
                    return [
                        'id' => $file->id,
                        'encrypted_file_id' => $file->encrypted_file_id,
                        'has_encrypted_file' => $file->encryptedFile !== null
                    ];
                })->toArray()
            ]);

            return view('data-access.shared-files', [
                'request' => $request->load('owner'),
                'files' => $files
            ]);

        } catch (\Exception $e) {
            Log::error('Error in viewSharedFiles', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to load shared files']);
        }
    }
}
