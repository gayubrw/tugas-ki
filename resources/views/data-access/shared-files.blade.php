<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Shared Files
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Request Information -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Request Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Shared by:</p>
                            <p class="font-medium">{{ $request->owner->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status:</p>
                            <p class="font-medium">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Approved at:</p>
                            <p class="font-medium">{{ $request->approved_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Expires at:</p>
                            <p class="font-medium">
                                {{ $request->expires_at->format('Y-m-d H:i:s') }}
                                <span class="text-gray-500 text-sm">({{ $request->expires_at->diffForHumans() }})</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Shared Files -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Shared Files</h3>

                    @if($files->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            File Name
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Size
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($files as $sharedFile)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $sharedFile->encryptedFile->original_name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $sharedFile->encryptedFile ? number_format($sharedFile->encryptedFile->file_size / 1024, 2) . ' KB' : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $sharedFile->encryptedFile ? strtoupper(pathinfo($sharedFile->encryptedFile->original_name, PATHINFO_EXTENSION)) : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($sharedFile->encryptedFile)
                                                <a href="{{ route('files.download-shared', $sharedFile->encryptedFile) }}"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                    Download
                                                </a>
                                            @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No files have been shared yet</h3>
                            <p class="mt-1 text-sm text-gray-500">The owner hasn't shared any files with you through this request.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
