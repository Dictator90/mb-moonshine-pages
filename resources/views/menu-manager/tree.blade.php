{{--
    The tabbed positions tree: a tab bar plus one panel per position. Rendered
    inside #mmgr-tree (see index.blade) so async actions can re-render just this
    region in place (innerHTML swap via MoonShine's DOMUpdater) without a full
    page reload. The `tab` Alpine state lives on the outer .mmgr element, so it
    survives a swap.

    Expects:
      $positions list of section data sets (each = position.blade variables)
--}}
@if(empty($positions))
    <div class="box"><div class="mmgr-empty">{{ __('moonshine-pages::moonshine-pages.menu_manager.empty') }}</div></div>
@else
    {{-- Positions rendered as tabs; only the active position's panel is shown. --}}
    <div class="mmgr-tabs">
        @foreach($positions as $section)
            <button
                type="button"
                class="mmgr-tab"
                :class="tab === {{ $section['position']['id'] }} ? 'mmgr-tab--active' : ''"
                @click="tab = {{ $section['position']['id'] }}"
            >{{ $section['position']['name'] }}</button>
        @endforeach
    </div>

    @foreach($positions as $section)
        <div x-show="tab === {{ $section['position']['id'] }}" x-cloak>
            @include('moonshine-pages::menu-manager.position', $section)
        </div>
    @endforeach
@endif
