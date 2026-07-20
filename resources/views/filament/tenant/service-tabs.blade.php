<div
    x-data="{
        activeTab: '{{ $activeKeys[0] ?? '' }}',
        activeKeys: {{ Js::from($activeKeys) }},
        allKeys: {{ Js::from($allKeys) }},
        setTab(key) { if (this.activeKeys.includes(key)) this.activeTab = key; },
        isActive(key) { return this.activeTab === key; },
        isVisible(key) { return this.activeKeys.includes(key); }
    }"
    x-init="if (!activeTab || !activeKeys.includes(activeTab)) { activeTab = activeKeys[0] || ''; }"
    x-on:active-services-changed.window="activeKeys = $event.detail; if (!activeKeys.includes(activeTab)) { activeTab = activeKeys[0] || ''; }"
>
    {{-- Tab bar --}}
    <nav class="flex gap-1 overflow-x-auto border-b border-gray-200 pb-px dark:border-gray-700">
        <template x-for="key in allKeys" :key="key">
            <button
                type="button"
                x-show="isVisible(key)"
                x-text="labels[key] || key"
                x-on:click="setTab(key)"
                x-bind:class="isActive(key)
                    ? 'border-primary-500 text-primary-600 -mb-px border-b-2 bg-white px-4 py-2.5 text-sm font-medium dark:bg-gray-800 dark:text-primary-400'
                    : 'border-transparent px-4 py-2.5 text-sm text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                class="shrink-0 rounded-t-lg transition"
            ></button>
        </template>
    </nav>

    {{-- Tab content --}}
    <div class="mt-4">
        @foreach ($tabs as $key => $content)
            <div x-show="isActive('{{ $key }}')" x-cloak>
                {!! $content !!}
            </div>
        @endforeach
    </div>
</div>

<script>
    // Labels for tab buttons
    window.labels = @json($labels);
</script>
