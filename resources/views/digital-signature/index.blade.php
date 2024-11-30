<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Digital Signature') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Generate Key Pair Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Generate Key Pair</h3>
                    @if(!$hasKeyPair)
                        <form action="{{ route('generate-key-pair') }}" method="POST">
                            @csrf
                            <div class="mt-4 flex justify-center">
                                <x-primary-button type="submit">
                                    {{ __('Generate New Key Pair') }}
                                </x-primary-button>
                            </div>
                        </form>
                    @else
                        <div class="rounded-md bg-green-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <!-- Heroicon name: solid/check-circle -->
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">
                                        You already have a key pair generated
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sign PDF Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Sign PDF Document</h3>
                    @if($hasKeyPair)
                        <form action="{{ route('sign-pdf') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mt-4">
                                <x-input-label for="pdf_file" :value="__('Select PDF File')" />
                                <input type="file"
                                       id="pdf_file"
                                       name="pdf_file"
                                       accept=".pdf"
                                       class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                       required />
                                <x-input-error :messages="$errors->get('pdf_file')" class="mt-2" />
                            </div>
                            <div class="mt-4 flex justify-center">
                                <x-primary-button type="submit">
                                    {{ __('Sign PDF') }}
                                </x-primary-button>
                            </div>
                        </form>
                    @else
                        <div class="rounded-md bg-yellow-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <!-- Heroicon name: solid/exclamation -->
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-yellow-800">
                                        Please generate a key pair first before signing documents
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Verify Signature Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Verify PDF Signature</h3>
                    <form action="{{ route('verify-signature') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mt-4">
                            <x-input-label for="verify_pdf_file" :value="__('Select Signed PDF File')" />
                            <input type="file"
                                   id="verify_pdf_file"
                                   name="pdf_file"
                                   accept=".pdf"
                                   class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                   required />
                            <x-input-error :messages="$errors->get('pdf_file')" class="mt-2" />
                        </div>
                        <div class="mt-4 flex justify-center">
                            <x-secondary-button type="submit">
                                {{ __('Verify Signature') }}
                            </x-secondary-button>
                        </div>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="mt-6 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mt-6 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
