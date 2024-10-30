<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Secure Data Storage Dashboard') }}
            </h2>
            <button onclick="window.location.href='{{ route('files.create') }}'"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Upload New File') }}
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Total Files Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm font-medium">Total Files</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">
                            {{ \App\Models\EncryptedFile::where('user_id', auth()->id())->count() }}
                        </div>
                        <div class="mt-2 text-sm text-gray-600">Encrypted documents in your storage</div>
                    </div>
                </div>

                <!-- Storage Usage -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm font-medium">Storage Used</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900">
                            {{ number_format(\App\Models\EncryptedFile::where('user_id', auth()->id())->sum('file_size') / 1048576, 2) }} MB
                        </div>
                        <div class="mt-2 text-sm text-gray-600">Total encrypted data size</div>
                    </div>
                </div>

                <!-- Encryption Methods -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm font-medium">Encryption Methods Used</div>
                        <div class="mt-2">
                            @php
                                $methods = \App\Models\EncryptedFile::where('user_id', auth()->id())
                                    ->select('encryption_algorithm')
                                    ->selectRaw('count(*) as count')
                                    ->groupBy('encryption_algorithm')
                                    ->get();
                            @endphp
                            @foreach($methods as $method)
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-medium">{{ strtoupper($method->encryption_algorithm) }}</span>
                                    <span class="text-sm text-gray-600">{{ $method->count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Files -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Files</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Encryption</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach(\App\Models\EncryptedFile::where('user_id', auth()->id())->latest()->take(5)->get() as $file)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $file->original_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ ucfirst($file->file_type) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ number_format($file->file_size / 1024, 2) }} KB
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ strtoupper($file->encryption_algorithm) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $file->created_at->diffForHumans() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('files.download', $file) }}" class="text-blue-600 hover:text-blue-900">Download</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-right">
                        <a href="{{ route('files.index') }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            View All Files →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Encryption Performance -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Encryption Performance Analysis</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @php
                            $performance = \App\Models\EncryptedFile::where('user_id', auth()->id())
                                ->select('encryption_algorithm')
                                ->selectRaw('AVG(encryption_time) as avg_encryption_time')
                                ->selectRaw('AVG(decryption_time) as avg_decryption_time')
                                ->groupBy('encryption_algorithm')
                                ->get();
                        @endphp
                        @foreach($performance as $stat)
                        <div class="border rounded p-4">
                            <h4 class="font-medium text-gray-900">{{ strtoupper($stat->encryption_algorithm) }}</h4>
                            <div class="mt-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Avg. Encryption:</span>
                                    <span>{{ number_format($stat->avg_encryption_time * 1000, 2) }} ms</span>
                                </div>
                                <div class="flex justify-between text-sm mt-1">
                                    <span class="text-gray-600">Avg. Decryption:</span>
                                    <span>{{ number_format($stat->avg_decryption_time * 1000, 2) }} ms</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-right">
                        <a href="{{ route('files.analysis') }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            View Detailed Analysis →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
