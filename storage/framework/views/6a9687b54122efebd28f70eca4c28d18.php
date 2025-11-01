<div class="bg-white p-4 shadow-md rounded-lg mb-6" x-data="{
    postType: 'music',
    searchQuery: '',
    searchResults: [],
    selectedTrack: null,
    loading: false,

    init() {
        this.$watch('searchQuery', () => this.search());
        this.$root.addEventListener('switchToMusicShare', () => {
            this.postType = 'music';
            this.searchQuery = '';
            this.searchResults = [];
            this.selectedTrack = null;
        });
    },
    search() {
        if (this.searchQuery.length < 3) {
            this.searchResults = [];
            return;
        }
        this.loading = true;
        fetch(`<?php echo e(route('spotify.search')); ?>?query=${encodeURIComponent(this.searchQuery)}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            this.searchResults = data;
            this.loading = false;
        })
        .catch(error => {
            console.error('Spotify search failed:', error);
            this.loading = false;
        });
    },
    selectTrack(track) {
        this.selectedTrack = track;
        this.searchQuery = ''; // Clear search field after selection
        this.searchResults = [];
    },
    getArtistNames(artists) {
        return artists.map(artist => artist.name).join(', ');
    },
    resetComposer() {
        this.postType = 'music';
        this.searchQuery = '';
        this.searchResults = [];
        this.selectedTrack = null;
        this.$refs.captionInput.value = '';
    }
}">
    <form method="POST" action="<?php echo e(route('shares.store')); ?>" @submit.prevent="$el.submit()">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="type" x-model="postType">

        <div>
            <h3 class="font-semibold mb-2">Search Spotify</h3>
            <?php if (isset($component)) { $__componentOriginal18c21970322f9e5c938bc954620c12bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal18c21970322f9e5c938bc954620c12bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.text-input','data' => ['type' => 'text','class' => 'w-full','placeholder' => 'Search for a track or artist...','xModel.debounce.500ms' => 'searchQuery','xInit' => '$watch(\'postType\', (val) => { if (val === \'music\') $el.focus() })']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'text','class' => 'w-full','placeholder' => 'Search for a track or artist...','x-model.debounce.500ms' => 'searchQuery','x-init' => '$watch(\'postType\', (val) => { if (val === \'music\') $el.focus() })']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $attributes = $__attributesOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__attributesOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal18c21970322f9e5c938bc954620c12bb)): ?>
<?php $component = $__componentOriginal18c21970322f9e5c938bc954620c12bb; ?>
<?php unset($__componentOriginal18c21970322f9e5c938bc954620c12bb); ?>
<?php endif; ?>

            <div x-show="searchQuery.length >= 3 && searchResults.length === 0 && !loading" class="mt-4 p-3 bg-gray-50 rounded">
                No tracks found for "<span x-text="searchQuery"></span>".
            </div>

            <div x-show="loading" class="text-center p-4">Loading...</div>

            <ul x-show="searchResults.length > 0 && !selectedTrack" class="mt-4 max-h-56 overflow-y-auto divide-y border rounded-lg">
                <template x-for="track in searchResults" :key="track.id">
                    <li @click="selectTrack(track)" class="p-3 flex items-center space-x-4 hover:bg-gray-100 cursor-pointer rounded-lg">
                        <img :src="track.album.images[0]?.url" alt="Album" class="w-10 h-10 rounded">
                        <div>
                            <div class="font-semibold text-sm" x-text="track.name"></div>
                            <div class="text-xs text-gray-600" x-text="getArtistNames(track.artists)"></div>
                        </div>
                    </li>
                </template>
            </ul>

            <div x-show="selectedTrack" class="mb-4 border border-green-300 bg-green-50/50 rounded-lg p-4 flex items-center space-x-4 mt-4">
                <img :src="selectedTrack?.album.images[0].url" alt="Album Art" class="w-16 h-16 rounded">
                <div>
                    <div class="font-bold text-lg" x-text="selectedTrack?.name"></div>
                    <div class="text-gray-600" x-text="getArtistNames(selectedTrack?.artists || [])"></div>
                </div>
                <button type="button" @click="selectedTrack = null; searchQuery=''" class="text-red-500 hover:text-red-700 ml-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <input type="hidden" name="spotify_track_id" x-model="selectedTrack?.id">

            <div class="mt-4">
                <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => 'caption','value' => __('Caption (optional)')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'caption','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Caption (optional)'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
                <textarea id="caption" name="caption" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
            </div>
        </div>

        <div class="mt-3 flex justify-end items-center">
            <?php if (isset($component)) { $__componentOriginald411d1792bd6cc877d687758b753742c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald411d1792bd6cc877d687758b753742c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.primary-button','data' => ['class' => 'bg-custom-mid-blue hover:bg-custom-dark-blue','xBind:disabled' => '!selectedTrack']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('primary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bg-custom-mid-blue hover:bg-custom-dark-blue','x-bind:disabled' => '!selectedTrack']); ?>
                Share Song
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $attributes = $__attributesOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__attributesOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald411d1792bd6cc877d687758b753742c)): ?>
<?php $component = $__componentOriginald411d1792bd6cc877d687758b753742c; ?>
<?php unset($__componentOriginald411d1792bd6cc877d687758b753742c); ?>
<?php endif; ?>
        </div>
    </form>
</div><?php /**PATH C:\laragon\www\musicsocial-main\resources\views/components/post-composer.blade.php ENDPATH**/ ?>