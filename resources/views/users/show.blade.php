@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">User Profile</h1>
                    <p class="mt-1 text-sm text-gray-600">Viewing details for {{ $user->name }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <div class="mt-1 text-gray-900">{{ $user->name }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <div class="mt-1 text-gray-900">{{ $user->email }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <div class="mt-1 text-gray-900">{{ $user->phone ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <div class="mt-1">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($user->role === 'super_admin') bg-purple-100 text-purple-800
                                @elseif($user->role === 'admin') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created At</label>
                        <div class="mt-1 text-gray-900">{{ $user->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Updated At</label>
                        <div class="mt-1 text-gray-900">{{ $user->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('users.index') }}" class="text-blue-600 hover:text-blue-900">
                        &larr; Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection