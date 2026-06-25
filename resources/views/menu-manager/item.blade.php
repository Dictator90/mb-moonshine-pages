{{--
    Single menu item rendered as a card + its (sortable) children container.
    Recursive.

    Expects:
      $item      array node (id,name,is_active,icon_url,source_type,source_label,
                 source_color,target_display,edit_url,children)
      $endpoints array of POST action URLs (toggleActive,duplicate,detach,destroy,attach,reorder)
      $position  array position (id,code,name,items)
--}}
@php
    $positionId = $position['id'];
    $hasChildren = ! empty($item['children']);
@endphp

<div
    class="mmgr-item"
    data-id="{{ $item['id'] }}"
    x-data="menuRow({
        id: {{ $item['id'] }},
        position: {{ $positionId }},
        active: {{ $item['is_active'] ? 'true' : 'false' }},
        open: {{ ($item['open'] ?? false) ? 'true' : 'false' }},
        endpoints: {
            toggleActive: '{{ $endpoints['toggleActive'] }}',
            toggleCollapse: '{{ $endpoints['toggleCollapse'] }}',
            duplicate: '{{ $endpoints['duplicate'] }}',
            detach: '{{ $endpoints['detach'] }}',
            destroy: '{{ $endpoints['destroy'] }}'
        }
    })"
    :class="active ? '' : 'mmgr-item--hidden'"
>
    <div class="mmgr-card">
        {{-- drag handle (only this initiates a drag — see data-handle) --}}
        <span class="mmgr-handle" title="">
            <x-moonshine::icon icon="bars-3" size="5" />
        </span>

        {{-- expand / collapse --}}
        @if($hasChildren)
            <button type="button" class="mmgr-toggle" @click="toggleOpen()">
                <span x-show="open" x-cloak><x-moonshine::icon icon="chevron-down" size="4" /></span>
                <span x-show="!open" x-cloak><x-moonshine::icon icon="chevron-right" size="4" /></span>
            </button>
        @else
            <span class="mmgr-toggle mmgr-toggle--empty"></span>
        @endif

        {{-- icon box --}}
        <span class="mmgr-iconbox">
            @if(! empty($item['icon_url']))
                <x-moonshine::img :src="$item['icon_url']" :alt="$item['name']" class="w-full h-full object-cover" />
            @else
                <x-moonshine::icon icon="document" size="4" class="text-gray-400 dark:text-gray-500" />
            @endif
        </span>

        {{-- name --}}
        <span class="mmgr-name">{{ $item['name'] }}</span>

        {{-- source-type badge --}}
        <x-moonshine::badge :color="$item['source_color']" class="shrink-0">{{ $item['source_label'] }}</x-moonshine::badge>

        {{-- target display --}}
        <span class="mmgr-target" title="{{ $item['target_display'] }}">{{ $item['target_display'] }}</span>

        {{-- status --}}
        <span class="mmgr-status" :class="active ? 'mmgr-status--on' : 'mmgr-status--off'">
            <span x-show="active">&#9679; {{ __('moonshine-pages::moonshine-pages.menu_manager.status.active') }}</span>
            <span x-show="!active" x-cloak>&#9675; {{ __('moonshine-pages::moonshine-pages.menu_manager.status.hidden') }}</span>
        </span>

        {{-- actions --}}
        <div class="mmgr-actions">
            <button type="button" class="btn btn-sm" title="{{ __('moonshine-pages::moonshine-pages.menu_manager.actions.edit') }}"
                @click="mmOpenForm(@js($item['edit_url']))">
                <x-moonshine::icon icon="pencil" size="4" />
            </button>

            <button type="button" class="btn btn-sm" @click="duplicate()" title="{{ __('moonshine-pages::moonshine-pages.menu_manager.actions.duplicate') }}">
                <x-moonshine::icon icon="document-duplicate" size="4" />
            </button>

            <button type="button" class="btn btn-sm" @click="toggle()"
                :title="active
                    ? '{{ __('moonshine-pages::moonshine-pages.menu_manager.actions.hide') }}'
                    : '{{ __('moonshine-pages::moonshine-pages.menu_manager.actions.show') }}'">
                <span x-show="active"><x-moonshine::icon icon="eye-slash" size="4" /></span>
                <span x-show="!active" x-cloak><x-moonshine::icon icon="eye" size="4" /></span>
            </button>

            <button type="button" class="btn btn-sm btn-warning" @click="detach()" title="{{ __('moonshine-pages::moonshine-pages.menu_manager.actions.detach') }}">
                <x-moonshine::icon icon="x-mark" size="4" />
            </button>

            <button type="button" class="btn btn-sm btn-error" @click="destroy()" title="{{ __('moonshine-pages::moonshine-pages.menu_manager.actions.delete') }}">
                <x-moonshine::icon icon="trash" size="4" />
            </button>
        </div>
    </div>

    {{-- children: always rendered (even when empty) so a leaf is a nest target.
         @foreach sits flush against the tags so a childless item is a truly empty
         <div> (CSS :empty). data-* options are forwarded to SortableJS. --}}
    <div class="mmgr-children" @if($hasChildren) x-show="open" x-cloak x-transition.opacity.duration.180ms @endif>
        <div
            class="mmgr-droplist"
            data-id="{{ $item['id'] }}"
            data-nest-hint="{{ __('moonshine-pages::moonshine-pages.menu_manager.nest_hint') }}"
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
            @foreach($item['children'] as $child)
                @include('moonshine-pages::menu-manager.item', ['item' => $child, 'endpoints' => $endpoints, 'position' => $position])
            @endforeach
        </div>
    </div>
</div>
