<div class="flex space-x-3" x-data="{ openReply: false, openEdit: false }">
    <img src="{{ $comment->user->profile_picture ? Storage::url($comment->user->profile_picture) : 'https://via.placeholder.com/150' }}"
         alt="avatar"
         class="h-10 w-10 rounded-full object-cover">
    <div class="flex-1">
        <div>
            <a href="{{ route('profile.show', $comment->user->name) }}" class="font-bold text-gray-900">{{ $comment->user->name }}</a>
            <span class="text-gray-500 text-sm"> &middot; {{ $comment->created_at->diffForHumans() }}</span>
        </div>
        <div x-show="!openEdit">
            <p class="mt-1 text-gray-800">
                {{ $comment->body }}
            </p>
        </div>

        <div x-show="openEdit" x-transition class="mt-2" style="display: none;" x-data="{ editedBody: '{{ $comment->body }}' }">
            <form @submit.prevent="
                fetch('{{ route('shares.comments.update', ['share' => $comment->share, 'comment' => $comment]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ body: editedBody, _method: 'PATCH' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.body) {
                        $el.closest('.flex-1').querySelector('p.mt-1.text-gray-800').innerText = data.body;
                        openEdit = false;
                    }
                })
            ">
                @csrf
                @method('PATCH')
                <textarea x-model="editedBody" name="body" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                <div class="mt-2 space-x-2">
                    <x-primary-button type="submit" class="bg-custom-mid-blue">Save</x-primary-button>
                    <button type="button" @click="openEdit = false" class="text-gray-500">Cancel</button>
                </div>
            </form>
        </div>

        <div class="mt-2 flex items-center space-x-4 text-sm">
            <button @click="openReply = !openReply" class="text-gray-500 hover:text-gray-900">Reply</button>
            @if ($comment->user->is(auth()->user()))
                <button @click="openEdit = !openEdit" class="text-gray-500 hover:text-gray-900">Edit</button>
                <form @submit.prevent="
                    if (!confirm('Are you sure you want to delete this comment?')) return;
                    fetch('{{ route('shares.comments.destroy', ['share' => $comment->share, 'comment' => $comment]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    })
                    .then(response => {
                        if (response.ok) {
                            $el.closest('.flex.space-x-3').remove();
                        }
                    })
                ">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                </form>
            @endif
        </div>

        <div x-show="openReply" x-transition class="mt-2" style="display: none;" x-data="{ newReply: '' }">
            <form @submit.prevent="
                fetch('{{ route('shares.comments.store', $comment->share) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        body: newReply,
                        parent_id: {{ $comment->id }}
                    })
                })
                .then(response => response.text())
                .then(html => {
                    let replySection = $el.parentElement.nextElementSibling;
                    replySection.insertAdjacentHTML('beforeend', html);
                    newReply = '';
                    openReply = false;
                })
            ">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <div class="flex items-center space-x-2">
                    <img src="{{ auth()->user()->profile_picture ? Storage::url(auth()->user()->profile_picture) : 'https://via.placeholder.com/150' }}" alt="your avatar" class="h-8 w-8 rounded-full">
                    <x-text-input x-model="newReply" name="body" class="block w-full" placeholder="Write a reply..." required />
                    <x-primary-button type="submit" class="bg-custom-mid-blue">Post</x-primary-button>
                </div>
            </form>
        </div>

        <div class="mt-4 space-y-4 pl-8 border-l-2 border-gray-200">
            @foreach ($comment->replies as $reply)
                <x-comment :comment="$reply" />
            @endforeach
        </div>
    </div>
</div>