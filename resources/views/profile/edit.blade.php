<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>
    <div class="py-5">
        <div class="container">
            <div class="row gy-4">
                <!-- Update Profile Information Form -->
                <div class="col-12 col-md-8 col-lg-6 mx-auto">
                    <div class="p-4 bg-white shadow rounded">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
    
                <!-- Update Password Form -->
                <div class="col-12 col-md-8 col-lg-6 mx-auto">
                    <div class="p-4 bg-white shadow rounded">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
    
                <!-- Delete User Form -->
                <div class="col-12 col-md-8 col-lg-6 mx-auto">
                    <div class="p-4 bg-white shadow rounded">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div> --}}
</x-app-layout>
