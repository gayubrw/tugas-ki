@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="text-center">
    <h1 class="text-4xl font-bold mb-4">Welcome to {{ config('app.name') }}</h1>
    <p class="text-lg text-gray-600">This is your application's homepage.</p>
</div>
@endsection
