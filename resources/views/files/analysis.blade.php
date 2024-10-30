<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Encryption Performance Analysis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Overall Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Overall Statistics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-blue-800 text-sm font-medium">Total Files</div>
                            <div class="mt-2 text-2xl font-bold text-blue-900">
                                {{ $totalStats['total_files'] }}
                            </div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="text-green-800 text-sm font-medium">Total Storage Used</div>
                            <div class="mt-2 text-2xl font-bold text-green-900">
                                {{ number_format($totalStats['total_size'] / 1048576, 2) }} MB
                            </div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="text-purple-800 text-sm font-medium">Average Encryption Time</div>
                            <div class="mt-2 text-2xl font-bold text-purple-900">
                                {{ number_format($totalStats['avg_encryption_time'] * 1000, 2) }} ms
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Algorithm Performance -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Algorithm Performance Comparison</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Algorithm</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Files</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. File Size</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Encryption Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. Decryption Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($analytics as $stat)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($stat->encryption_algorithm == 'aes') bg-blue-100 text-blue-800
                                            @elseif($stat->encryption_algorithm == 'des') bg-green-100 text-green-800
                                            @else bg-purple-100 text-purple-800 @endif">
                                            {{ strtoupper($stat->encryption_algorithm) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $stat->total_files }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($stat->avg_file_size / 1024, 2) }} KB
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($stat->avg_encryption_time * 1000, 2) }} ms
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($stat->avg_decryption_time * 1000, 2) }} ms
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- File Type Distribution -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">File Type Distribution</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($fileTypeStats as $stat)
                        <div class="border rounded-lg p-4">
                            <div class="text-gray-600 text-sm font-medium">{{ ucfirst($stat->file_type) }}</div>
                            <div class="mt-2 grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500">Total Files</div>
                                    <div class="text-lg font-semibold">{{ $stat->total }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Avg. Size</div>
                                    <div class="text-lg font-semibold">{{ number_format($stat->avg_size / 1024, 2) }} KB</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Tambahkan visualisasi grafik jika diperlukan
    </script>
    @endpush
</x-app-layout>
