{{--
    Menu management page. Renders one section per menu position.

    Expects:
      $title     string
      $positions list of section data sets (each = position.blade variables)
      $createUrl string (Menu create-form URL; per-section value preferred)
--}}
@php $firstTab = $positions[0]['position']['id'] ?? null; @endphp
<div class="mmgr" x-data="{ tab: @js($firstTab) }">
    {{-- Tabbed positions tree. --}}
    @include('moonshine-pages::menu-manager.tree', ['positions' => $positions])

    {{-- Shared async modal: hosts the reused Menu resource create/edit form,
         loaded on demand via window.MoonShine.ui.toggleModal('mm-form', url). --}}
    <x-moonshine::modal
        name="mm-form"
        :async-url="$createUrl"
        wide
        data-always-load="true"
        data-mm-form="1"
        :title="__('moonshine-pages::moonshine-pages.menu.resource_title')"
    />
</div>

<style>
    [x-cloak] { display: none !important; }

    /* Tokens mapped onto MoonShine's native palette variables. These already
       flip for dark mode via MoonShine's theme, so no manual .dark overrides. */
    .mmgr {
        --mmgr-border: var(--color-base-stroke);
        --mmgr-card: var(--color-base);
        --mmgr-card-hover: var(--color-base-100);
        --mmgr-muted: var(--color-gray-500);
        --mmgr-accent: var(--color-primary);
        --mmgr-accent-soft: color-mix(in oklch, var(--color-primary) 12%, transparent);
    }

    /* ── Position tabs ── */
    .mmgr-tabs {
        display: flex; flex-wrap: wrap; gap: .25rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid var(--mmgr-border);
    }
    .mmgr-tab {
        padding: .55rem .95rem; margin-bottom: -1px;
        font-weight: 600; font-size: .92rem; color: var(--mmgr-muted);
        background: none; border: 0; border-bottom: 2px solid transparent;
        cursor: pointer; white-space: nowrap;
        transition: color .15s ease, border-color .15s ease;
    }
    .mmgr-tab:hover { color: var(--mmgr-accent); }
    .mmgr-tab--active { color: var(--mmgr-accent); border-bottom-color: var(--mmgr-accent); }

    /* ── Position box ── */
    .mmgr-position { margin-bottom: 1.25rem; }
    .mmgr-pos-actions {
        display: flex; align-items: center; gap: .5rem;
        padding-bottom: .75rem; margin-bottom: .5rem;
        border-bottom: 1px solid var(--mmgr-border);
    }
    .mmgr-empty { text-align: center; padding: 1.1rem; font-size: .85rem; color: var(--mmgr-muted); }

    /* ── Item card ── */
    .mmgr-item { list-style: none; }
    .mmgr-card {
        display: flex; align-items: center; gap: .5rem;
        padding: .4rem .55rem; margin: .35rem 0;
        border: 1px solid var(--mmgr-border); border-radius: .6rem;
        background: var(--mmgr-card);
        transition: background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .08s ease;
    }
    .mmgr-card:hover { background: var(--mmgr-card-hover); box-shadow: 0 1px 5px rgba(0, 0, 0, .06); }
    /* A hidden item dims its own row AND its whole subtree, so hiding a parent
       visibly fades all its descendants. Descendant (not child) selector. */
    .mmgr-item--hidden .mmgr-card { opacity: .55; }

    .mmgr-handle { flex-shrink: 0; display: flex; align-items: center; cursor: grab; color: var(--mmgr-muted); touch-action: none; }
    .mmgr-handle:active { cursor: grabbing; }
    .mmgr-toggle { flex-shrink: 0; width: 1.1rem; display: flex; justify-content: center; align-items: center; background: none; border: 0; padding: 0; cursor: pointer; color: var(--mmgr-muted); }
    .mmgr-toggle--empty { cursor: default; display: none; }
    .mmgr-iconbox {
        flex-shrink: 0; width: 1.75rem; height: 1.75rem; border-radius: .4rem;
        border: 1.5px dashed var(--mmgr-border); display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    /* name + badge + target + status cluster on the left next to each other;
       actions are pushed to the right edge via margin-left:auto. */
    .mmgr-name { flex: 0 1 auto; min-width: 0; width:20%; font-weight: 600; font-size: .9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mmgr-target { flex-shrink: 0; max-width: 170px; font-size: .82rem; color: var(--mmgr-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .mmgr-status { flex-shrink: 0; font-size: .72rem; white-space: nowrap; }
    .mmgr-status--on { color: var(--color-success-text); }
    .mmgr-status--off { color: var(--mmgr-muted); }
    .mmgr-actions { display: flex; align-items: center; gap: .2rem; flex-shrink: 0; margin-left: auto; }

    /* ── Tree nesting ── */
    .mmgr-children { margin-left: 1.35rem; padding-left: .6rem; border-left: 2px dashed var(--mmgr-border); }
    .mmgr-droplist { min-height: 2px; transition: min-height .15s ease; }

    /* While dragging, empty child lists become subtle, targetable drop zones. */
    body.mmgr-dragging .mmgr-children .mmgr-droplist:empty {
        min-height: 26px; margin: .25rem 0;
        border: 1.5px dashed transparent; border-radius: .5rem;
        transition: all .15s ease;
    }
    /* The zone the cursor is over lights up + invites nesting ("drop to nest"). */
    .mmgr-droplist.mmgr-drop-active {
        min-height: 30px; display: flex; align-items: center;
        border-color: var(--mmgr-accent) !important; background: var(--mmgr-accent-soft);
    }
    .mmgr-droplist.mmgr-drop-active::before {
        content: attr(data-nest-hint);
        font-size: 11px; font-weight: 600; padding-left: 10px;
        color: var(--mmgr-accent); pointer-events: none;
    }

    /* ── Drag visuals (SortableJS classes) ── */
    .mmgr-ghost { opacity: .55; }
    .mmgr-ghost > .mmgr-card {
        border-style: dashed; border-color: var(--mmgr-accent);
        background: var(--mmgr-accent-soft); box-shadow: none;
    }
    .mmgr-chosen > .mmgr-card { box-shadow: 0 6px 18px rgba(0, 0, 0, .14); }
    .mmgr-drag > .mmgr-card { transform: rotate(.5deg); }

    /* ── Add-from-existing list (rendered inside MoonShine's native modal, which
         supplies the chrome; these only style the search box + candidate rows) ── */
    .mmgr-add-search {
        display: flex; align-items: center; gap: .5rem;
        padding: .5rem .75rem; margin-bottom: .75rem;
        border: 1px solid var(--mmgr-border); border-radius: .5rem;
        background: var(--color-base-100); color: var(--mmgr-muted);
    }
    .mmgr-add-search input {
        flex: 1; min-width: 0; background: transparent; border: 0; outline: none;
        font-size: .875rem; padding: 0; color: var(--color-base-text);
    }
    .mmgr-add-list { display: flex; flex-direction: column; gap: .25rem; max-height: 50vh; overflow-y: auto; }
    .mmgr-add-grouplabel {
        font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
        color: var(--mmgr-muted); padding: .25rem 0;
    }
    .mmgr-add-row { display: flex; align-items: center; gap: .75rem; padding: .5rem; border-radius: .5rem; }
    .mmgr-add-row--on { cursor: pointer; }
    .mmgr-add-row--on:hover { background: var(--mmgr-card-hover); }
    .mmgr-add-row--off { opacity: .45; cursor: not-allowed; }
    .mmgr-add-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: .875rem; font-weight: 600; }
    .mmgr-add-meta { flex-shrink: 0; font-size: .72rem; color: var(--mmgr-muted); }
    .mmgr-add-meta--on { color: var(--color-success-text); }
    .mmgr-add-empty { padding: 1rem; text-align: center; font-size: .875rem; color: var(--mmgr-muted); }
    .mmgr-add-foot {
        display: flex; align-items: center; justify-content: flex-end; gap: .75rem;
        margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--mmgr-border);
    }
</style>

<script>
    /*
     * Open the reused Menu resource create/edit form in the shared async modal.
     * After a SUCCESSFUL save (detected via the success toast) the manager is
     * reloaded so new/edited items appear; cancelling does not reload.
     */
    window.mmOpenForm = function (url) {
        const dataOf = () => {
            const el = document.querySelector('[data-mm-form]');
            return el && el._x_dataStack ? el._x_dataStack[0] : null;
        };

        let saved = false;
        const onToast = (e) => {
            const type = e?.detail?.type;
            if (type === 'success' || type === 'default' || type === 'info') {
                saved = true;
            }
        };
        window.addEventListener('toast', onToast);

        window.MoonShine.ui.toggleModal('mm-form', url);

        let wasOpen = false;
        const timer = setInterval(() => {
            const d = dataOf();
            if (!d) {
                return;
            }
            if (d.open) {
                wasOpen = true;
            } else if (wasOpen) {
                clearInterval(timer);
                window.removeEventListener('toast', onToast);
                if (saved) {
                    window.location.reload();
                }
            }
        }, 250);
    };

    /*
     * Drag affordances. A body flag turns empty child lists into subtle drop
     * targets; on dragover, only the child list under the cursor lights up with
     * the "drop here to nest" hint — so nesting feedback appears next to the
     * hovered item, not everywhere.
     */
    let mmActiveDrop = null;
    const mmClearActive = () => {
        if (mmActiveDrop) {
            mmActiveDrop.classList.remove('mmgr-drop-active');
            mmActiveDrop = null;
        }
    };

    document.addEventListener('dragstart', (e) => {
        if (e.target && e.target.closest && e.target.closest('.mmgr-item')) {
            document.body.classList.add('mmgr-dragging');
        }
    });

    // Debounced: dragover fires on every pixel, so the nest-target lookup is
    // throttled to one run per animation frame to keep dragging smooth.
    let mmDragThrottled = false;
    document.addEventListener('dragover', (e) => {
        if (!document.body.classList.contains('mmgr-dragging') || mmDragThrottled) {
            return;
        }
        mmDragThrottled = true;
        const el = e.target;
        requestAnimationFrame(() => {
            mmDragThrottled = false;
            const dl = el && el.closest ? el.closest('.mmgr-droplist') : null;
            // Only a nested (child) list that is still empty is a "nest under item" target.
            const target = (dl && dl.closest('.mmgr-children')
                && dl.querySelectorAll(':scope > .mmgr-item').length === 0) ? dl : null;
            if (target !== mmActiveDrop) {
                mmClearActive();
                if (target) {
                    target.classList.add('mmgr-drop-active');
                    mmActiveDrop = target;
                }
            }
        });
    });

    ['dragend', 'drop', 'mouseup'].forEach((ev) => {
        document.addEventListener(ev, () => {
            document.body.classList.remove('mmgr-dragging');
            mmClearActive();
        });
    });

    document.addEventListener('alpine:init', () => {
        /* Shared helper: posts to a method endpoint via MoonShine.request (CSRF handled). */
        const post = (ctx, url, body) => window.MoonShine.request(ctx, url, 'post', body);

        const CONFIRM_DETACH = @js(__('moonshine-pages::moonshine-pages.menu_manager.confirm.detach'));
        const CONFIRM_DELETE = @js(__('moonshine-pages::moonshine-pages.menu_manager.confirm.delete'));

        /* One Alpine component per item row. Endpoints are passed in from the row markup. */
        Alpine.data('menuRow', ({ id, position, active, open, endpoints }) => ({
            id,
            position,
            active,
            endpoints,
            open: !!open,
            loading: false,

            body() {
                return { id: this.id, position: this.position };
            },

            // Expand/collapse, persisting the new state to the user's session so
            // the tree remembers it across reloads (default: collapsed).
            toggleOpen() {
                this.open = ! this.open;
                post(this, this.endpoints.toggleCollapse, { id: this.id, open: this.open });
            },

            toggle() {
                // optimistic: flip immediately, then persist.
                this.active = !this.active;
                post(this, this.endpoints.toggleActive, this.body());
            },

            duplicate() {
                post(this, this.endpoints.duplicate, this.body());
            },

            detach() {
                if (!confirm(CONFIRM_DETACH)) {
                    return;
                }
                post(this, this.endpoints.detach, this.body());
            },

            destroy() {
                if (!confirm(CONFIRM_DELETE)) {
                    return;
                }
                post(this, this.endpoints.destroy, this.body());
            },
        }));

        /* One Alpine component per position: drives the "add from existing" modal. */
        Alpine.data('menuAdd', ({ position, attachUrl, available }) => ({
            position,
            attachUrl,
            available: available || [],
            search: '',
            selected: [],
            loading: false,

            get filtered() {
                const q = this.search.trim().toLowerCase();
                if (q === '') {
                    return this.available;
                }
                return this.available.filter(row => (row.name || '').toLowerCase().includes(q));
            },

            submit() {
                if (this.selected.length === 0) {
                    return;
                }
                post(this, this.attachUrl, { position: this.position, ids: this.selected });
            },
        }));
    });
</script>
