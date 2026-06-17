{{--
    One menu position rendered as a native MoonShine box: header (name + actions,
    no icon), the item tree, a footer, and the "add from existing" modal.

    Expects:
      $position  ['id','code','name','items']
      $available list of selectable items for the modal
      $endpoints POST action URLs
      $createUrl Menu create-form fragment URL
--}}
@php
    $positionId = $position['id'];
@endphp

<div
    class="box space-elements mmgr-position"
    x-data="menuAdd({ position: {{ $positionId }}, attachUrl: '{{ $endpoints['attach'] }}', available: @js($available) })"
>
    {{-- actions: shown right under the tab bar (no position name — it's the tab) --}}
    <div class="mmgr-pos-actions">
        <button type="button" class="btn btn-sm btn-primary" @click="mmOpenForm(@js($createUrl))">
            {{ __('moonshine-pages::moonshine-pages.menu_manager.create_item') }}
        </button>
        <button type="button" class="btn btn-sm" @click="modalOpen = true">
            {{ __('moonshine-pages::moonshine-pages.menu_manager.add_existing') }}
        </button>
    </div>

    @if(empty($position['items']))
        <div class="mmgr-empty">{{ __('moonshine-pages::moonshine-pages.menu_manager.empty') }}</div>
    @endif

    {{-- root sortable list: NO data-id so dropped items become root (parent null).
         data-* (non-async) attributes are forwarded to SortableJS options by the
         MoonShine sortable helper: animation, nested-drop tuning, drag classes. --}}
    <div
        class="mmgr-droplist mmgr-root"
        data-id=""
        x-data="sortable('{{ $endpoints['reorder'] }}&position={{ $positionId }}', 'ms-pos-{{ $positionId }}')"
        x-init="init()"
        data-animation="160"
        data-fallback-on-body="true"
        data-swap-threshold="0.6"
        data-empty-insert-threshold="26"
        data-handle=".mmgr-handle"
        data-ghost-class="mmgr-ghost"
        data-chosen-class="mmgr-chosen"
        data-drag-class="mmgr-drag"
    >
        @foreach($position['items'] as $item)
            @include('moonshine-pages::menu-manager.item', [
                'item' => $item,
                'endpoints' => $endpoints,
                'position' => $position,
            ])
        @endforeach
    </div>

    {{-- ── add-from-existing modal ── --}}
    <div
        x-show="modalOpen"
        x-cloak
        class="mmgr-modal-overlay"
        @click.self="close()"
        @keydown.escape.window="close()"
    >
        <div class="mmgr-modal-panel">
            {{-- modal header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
                <span class="text-base font-bold">{{ __('moonshine-pages::moonshine-pages.menu_manager.modal.title') }}</span>
                <button type="button" class="btn-fit text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="close()">
                    <x-moonshine::icon icon="x-mark" size="5" />
                </button>
            </div>

            {{-- search --}}
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 shrink-0">
                <div class="flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-3 py-2">
                    <x-moonshine::icon icon="magnifying-glass" size="4" class="text-gray-400 dark:text-gray-500" />
                    <input
                        type="text"
                        x-model="search"
                        placeholder="{{ __('moonshine-pages::moonshine-pages.menu_manager.modal.search') }}"
                        class="flex-1 bg-transparent border-0 outline-none text-sm p-0 focus:ring-0"
                    >
                </div>
            </div>

            {{-- list --}}
            <div class="mmgr-modal-body px-5 py-3 flex flex-col gap-1">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500 py-1">
                    {{ __('moonshine-pages::moonshine-pages.menu_manager.modal.available') }}
                </div>

                <template x-for="row in filtered" :key="row.id">
                    <label
                        class="flex items-center gap-3 px-2 py-2 rounded-lg"
                        :class="row.already
                            ? 'opacity-45 cursor-not-allowed'
                            : 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700'"
                    >
                        <input
                            type="checkbox"
                            class="shrink-0"
                            :value="row.id"
                            :disabled="row.already"
                            x-model.number="selected"
                        >
                        <span class="flex-1 min-w-0 truncate text-sm font-semibold" x-text="row.name"></span>
                        <span class="badge shrink-0" :class="'badge-' + row.source_color" x-text="row.source_label"></span>
                        <template x-if="row.already">
                            <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">{{ __('moonshine-pages::moonshine-pages.menu_manager.modal.already') }}</span>
                        </template>
                        <template x-if="!row.already">
                            <span class="text-xs shrink-0" :class="row.is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500'">
                                <span x-show="row.is_active">&#9679; {{ __('moonshine-pages::moonshine-pages.menu_manager.status.active') }}</span>
                                <span x-show="!row.is_active">&#9675; {{ __('moonshine-pages::moonshine-pages.menu_manager.status.hidden') }}</span>
                            </span>
                        </template>
                    </label>
                </template>

                <div x-show="filtered.length === 0" class="px-2 py-4 text-center text-sm text-gray-400 dark:text-gray-500">
                    {{ __('moonshine-pages::moonshine-pages.menu_manager.modal.empty') }}
                </div>
            </div>

            {{-- modal footer --}}
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 shrink-0">
                <button type="button" class="btn" @click="close()">
                    {{ __('moonshine-pages::moonshine-pages.menu_manager.modal.cancel') }}
                </button>
                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="selected.length === 0"
                    :class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                    @click="submit()"
                >
                    {{ __('moonshine-pages::moonshine-pages.menu_manager.modal.add_selected') }} (<span x-text="selected.length"></span>)
                </button>
            </div>
        </div>
    </div>
</div>
