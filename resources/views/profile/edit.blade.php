<x-app-layout pageTitle="{{ __('Profile Settings') }}">

    <div class="py-4 sm:py-12 bg-gray-100 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-12 gap-6 md:gap-8">

            <div class="hidden md:block col-span-2">
                <div class="sticky top-0 pt-4">
                    @php
                        $isHome = Route::is('dashboard');
                        $isProfile = true;
                        $isSettings = false;
                        $baseClasses = 'flex items-center p-3 rounded-full font-semibold text-lg hover:bg-gray-200 transition duration-150';
                        $activeClasses = ' !text-custom-dark-blue !font-bold bg-custom-periwinkle/50';
                    @endphp
                    @include('layouts.navigation-social')
                </div>
            </div>
            <div class="col-span-12 md:col-span-10">
                <div class="grid grid-cols-12 gap-6">

                    <div class="col-span-12 lg:col-span-8 space-y-6">

                        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                            @include('profile.partials.update-profile-information-form')
                        </div>

                        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                            @include('profile.partials.update-password-form')
                        </div>

                        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>

                    <div class="hidden lg:block col-span-4">
                        <div class="sticky top-0 pt-4">
                            @include('layouts.sidebar-right')
                        </div>
                    </div>

                </div>
            </div>
            </div>
    </div>
</x-app-layout>