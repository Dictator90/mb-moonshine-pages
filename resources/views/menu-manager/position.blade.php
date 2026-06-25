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
        <button type="button" class="btn btn-sm" @click="window.MoonShine.ui.toggleModal('mm-add-{{ $positionId }}')">
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

    {{-- ── add-from-existing modal (native MoonShine modal; the slot keeps the
         menuAdd Alpine scope through x-teleport) ── --}}
    <x-moonshine::modal
        name="mm-add-{{ $positionId }}"
        :title="__('moonshine-pages::moonshine-pages.menu_manager.modal.title')"
    >
        {{-- search --}}
        <div class="mmgr-add-search">
            <x-moonshine::icon icon="magnifying-glass" size="4" />
            <input
                type="text"
                x-model="search"
                placeholder="{{ __('moonshine-pages::moonshine-pages.menu_manager.modal.search') }}"
            >
        </div>

        {{-- list --}}
        <div class="mmgr-add-list">
            <div class="mmgr-add-grouplabel">
                {{ __('moonshine-pages::moonshine-pages.menu_manager.modal.available') }}
            </div>

            <template x-for="row in filtered" :key="row.id">
                <label
                    class="mmgr-add-row"
                    :class="row.already ? 'mmgr-add-row--off' : 'mmgr-add-row--on'"
                >
                    <input
                        type="checkbox"
                        class="shrink-0"
                        :value="row.id"
                        :disabled="row.already"
                        x-model.number="selected"
                    >
                    <span class="mmgr-add-name" x-text="row.name"></span>
                    <span class="badge shrink-0" :class="'badge-' + row.source_color" x-text="row.source_label"></span>
                    <template x-if="row.already">
                        <span class="mmgr-add-meta">{{ __('moonshine-pages::moonshine-pages.menu_manager.modal.already') }}</span>
                    </template>
                    <template x-if="!row.already">
                        <span class="mmgr-add-meta" :class="row.is_active ? 'mmgr-add-meta--on' : ''">
                            <span x-show="row.is_active">&#9679; {{ __('moonshine-pages::moonshine-pages.menu_manager.status.active') }}</span>
                            <span x-show="!row.is_active">&#9675; {{ __('moonshine-pages::moonshine-pages.menu_manager.status.hidden') }}</span>
                        </span>
                    </template>
                </label>
            </template>

            <div x-show="filtered.length === 0" class="mmgr-add-empty">
                {{ __('moonshine-pages::moonshine-pages.menu_manager.modal.empty') }}
            </div>
        </div>

        {{-- footer --}}
        <div class="mmgr-add-foot">
            <button type="button" class="btn" @click="toggleModal">
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
    </x-moonshine::modal>
</div>
