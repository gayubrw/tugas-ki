<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Access Request Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <!-- Request Information -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Request Information</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">From:</p>
                            <p class="font-medium">{{ $request->requester->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status:</p>
                            <p>
                                <span class="px-2 py-1 text-sm rounded-full {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Message:</p>
                            <p class="font-medium">{{ $request->message }}</p>
                        </div>
                    </div>
                </div>

                @if($request->status === 'pending')
                    <!-- File Selection Form -->
                    <form id="approvalForm" action="{{ route('data-access.approve', $request) }}" method="POST">
                        @csrf
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Select Files to Share</h3>

                            @error('files')
                                <div class="text-red-600 text-sm mb-4">{{ $message }}</div>
                            @enderror

                            @if($files->count() > 0)
                                <div class="space-y-4 mb-6">
                                    @foreach($files as $file)
                                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded hover:bg-gray-100">
                                            <input type="checkbox"
                                                   name="files[]"
                                                   value="{{ $file->id }}"
                                                   id="file_{{ $file->id }}"
                                                   class="file-checkbox rounded border-gray-300">
                                            <label for="file_{{ $file->id }}" class="flex flex-1 justify-between cursor-pointer">
                                                <span>{{ $file->original_name }}</span>
                                                <span class="text-gray-500">{{ number_format($file->file_size / 1024, 2) }} KB</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mb-6">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                               id="selectAll"
                                               class="rounded border-gray-300">
                                        <span class="ml-2">Select All Files</span>
                                    </label>
                                </div>

                                <input type="hidden" name="action" id="actionInput">

                                <div class="flex space-x-4">
                                    <button type="submit"
                                            onclick="return submitForm('approve')"
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Approve and Share Files
                                    </button>
                                    <button type="submit"
                                            onclick="return submitForm('reject')"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Reject Request
                                    </button>
                                </div>
                            @else
                                <p class="text-gray-500">No files available to share.</p>
                            @endif
                        </div>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const selectAllCheckbox = document.getElementById('selectAll');
                            const fileCheckboxes = document.querySelectorAll('.file-checkbox');
                            const form = document.getElementById('approvalForm');
                            const actionInput = document.getElementById('actionInput');

                            // Handle Select All functionality
                            if (selectAllCheckbox) {
                                selectAllCheckbox.addEventListener('change', function() {
                                    fileCheckboxes.forEach(checkbox => {
                                        checkbox.checked = this.checked;
                                    });
                                });

                                // Update Select All when individual checkboxes change
                                fileCheckboxes.forEach(checkbox => {
                                    checkbox.addEventListener('change', function() {
                                        const allChecked = Array.from(fileCheckboxes).every(cb => cb.checked);
                                        selectAllCheckbox.checked = allChecked;
                                    });
                                });
                            }
                        });

                        function submitForm(action) {
                            event.preventDefault();

                            // Set the action
                            document.getElementById('actionInput').value = action;

                            // For approve action, validate file selection
                            if (action === 'approve') {
                                const selectedFiles = document.querySelectorAll('.file-checkbox:checked');
                                if (selectedFiles.length === 0) {
                                    alert('Please select at least one file to share.');
                                    return false;
                                }
                            }

                            // Submit the form
                            document.getElementById('approvalForm').submit();
                            return false;
                        }
                    </script>
                @else
                    <div class="p-6">
                        <div class="bg-gray-50 rounded p-4">
                            <p class="text-gray-600">
                                This request has been {{ $request->status }}.
                                @if($request->status === 'approved')
                                    Files were shared on {{ $request->approved_at->format('Y-m-d H:i:s') }}.
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
