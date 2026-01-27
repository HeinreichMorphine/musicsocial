<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information, email address, and avatar.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.picture.update') }}" class="mt-6 space-y-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <h3 class="font-medium text-gray-700 dark:text-gray-300">{{ __('Profile Avatar') }}</h3>

        <div>
            <x-input-label for="profile_picture" :value="__('Current Avatar')" />

            <div class="mt-2 flex items-center space-x-4" x-data="{ photoPreview: null }">
                <!-- Profile Avatar Preview -->
                <div class="relative w-20 h-20">
                    <img 
                        x-bind:src="photoPreview"
                        x-show="photoPreview"
                        alt="{{ $user->name }}"
                        class="w-20 h-20 rounded-full object-cover border-2 border-gray-300 shadow-sm"
                        style="display: none;"
                    >
                    <img 
                        src="{{ Auth::user()->profile_picture ? Storage::url(Auth::user()->profile_picture) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&color=7F9CF5&background=EBF4FF' }}"
                        alt="{{ $user->name }}"
                        x-show="!photoPreview"
                        onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF';"
                        class="w-20 h-20 rounded-full object-cover border-2 border-gray-300 shadow-sm"
                    >
                </div>

                <div class="space-y-1">
                    <x-input-label for="profile_picture" :value="__('Upload New Avatar (Max 2MB)')" class="font-normal" />
                    <input 
                        id="profile_picture" 
                        name="profile_picture" 
                        type="file" 
                        class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" 
                        accept="image/*"
                        required 
                        @change="photoPreview = URL.createObjectURL($event.target.files[0])"
                    />
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('profile_picture')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Avatar') }}</x-primary-button>

            @if (session('status') === 'avatar-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600"
                >{{ __('Avatar saved.') }}</p>
            @endif
        </div>
    </form>

    <div class="border-t border-gray-200 my-6"></div>

    <form method="post" action="{{ route('profile.banner.update') }}" class="mt-6 space-y-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <h3 class="font-medium text-gray-700 dark:text-gray-300">{{ __('Profile Banner') }}</h3>

        <div>
            <!-- Current Banner Preview -->
            @if($user->cover_photo_path)
                <div class="mb-4">
                     <p class="text-sm text-gray-500 mb-1">Current Banner:</p>
                     <img src="{{ $user->cover_photo_url }}" alt="Current Banner" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                </div>
            @endif

            <x-input-label for="cover_photo" :value="__('Upload New Banner (Max 4MB)')" />
            
            <input id="cover_photo" name="cover_photo" type="file" class="mt-2 block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-900 focus:outline-none" required />
            <x-input-error class="mt-2" :messages="$errors->get('cover_photo')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Banner') }}</x-primary-button>

            @if (session('status') === 'cover-photo-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600"
                >{{ __('Banner saved.') }}</p>
            @endif
        </div>
    </form>

    <div class="border-t border-gray-200 my-6"></div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <!-- Name Form -->
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Name') }}</x-primary-button>

            @if (session('status') === 'profile-updated' && !$errors->has('email')) 
                {{-- Rudimentary check: if profile updated and no email errors, assume it might be name. 
                     Ideally we'd pass a specific status, but 'profile-updated' is generic. --}}
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Name saved.') }}</p>
            @endif
        </div>
    </form>

    <div class="border-t border-gray-200 my-6"></div>

    <!-- Email Form -->
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save Email') }}</x-primary-button>

            @if (session('status') === 'profile-updated' && !$errors->has('name'))
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Email saved.') }}</p>
            @endif
        </div>
    </form>
</section>