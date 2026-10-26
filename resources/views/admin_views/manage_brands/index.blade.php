@extends('layouts.admin')

@section('title', 'Manage Brands')

@section('content')
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Brands</h3>
                </div>
            </div>
            <div class="col-12 col-lg-auto mb-20">
                <a class="button button-primary" href="{{ route('manageBrands.create') }}"><span>Add Brand</span></a>
            </div>
        </div>

        <div class="col-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Brands</h4>
                    <small class="text-muted">Drag a brand up or down to change its place in the shop's Brands menu and brand row.</small>
                </div>
                <div class="box-body">
                    <table class="table table-bordered" data-ajax-list="list">
                        <thead>
                        <tr>
                            <th style="width: 110px;">Logo</th>
                            <th>Brand</th>
                            <th>Products</th>
                            <th>Order</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody data-reorder-url="{{ route('manageBrands.reorder') }}">
                        @forelse($brands as $brand)
                            <tr data-id="{{ $brand->id }}">
                                <td>
                                    @if($brand->logoUrl())
                                        <span class="brand-logo">
                                            <img src="{{ $brand->logoUrl() }}" alt="">
                                        </span>
                                    @else
                                        <span class="text-muted">No logo</span>
                                    @endif
                                </td>
                                <td>{{ $brand->name }}</td>
                                <td>{{ $brand->products_count }}</td>
                                <td>
                                    <span class="brand-order">
                                        <button type="button" class="brand-order__handle" data-brand-handle title="Drag to move (or use the arrow keys)" aria-label="Move {{ $brand->name }}: drag, or press the up and down arrow keys">
                                            <svg viewBox="0 0 16 16" width="16" height="16" fill="currentColor" aria-hidden="true"><circle cx="5.5" cy="3.5" r="1.4"/><circle cx="10.5" cy="3.5" r="1.4"/><circle cx="5.5" cy="8" r="1.4"/><circle cx="10.5" cy="8" r="1.4"/><circle cx="5.5" cy="12.5" r="1.4"/><circle cx="10.5" cy="12.5" r="1.4"/></svg>
                                        </button>
                                        <span class="brand-order__n">{{ $brand->sort_order }}</span>
                                    </span>
                                </td>
                                <td>
                                    <a class="button button-info" href="{{ route('manageBrands.edit', ['id' => $brand->id]) }}"><span>Edit</span></a>
                                    <button type="button" class="button button-danger" data-delete-brand="{{ route('manageBrands.destroy', ['id' => $brand->id]) }}"
                                            data-name="{{ $brand->name }}" data-products="{{ $brand->products_count }}"><span>Delete</span></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No brands yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection

@push('styles')
<style>
    .brand-logo { display: inline-grid; place-items: center; width: 90px; height: 48px; background: #fff; border-radius: 8px; padding: 4px; }
    .content-body .table .brand-logo img { width: auto; height: auto; max-width: 100%; max-height: 100%; object-fit: contain; border: 0; padding: 0; border-radius: 0; background: none; }
    .brand-order { display: inline-flex; align-items: center; gap: 10px; }
    .brand-order__handle { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; padding: 0;
                           border: 1px solid var(--ad-border); border-radius: 8px; background: var(--ad-surface-2); color: var(--ad-muted);
                           cursor: grab; touch-action: none; transition: background .12s, color .12s; }
    .brand-order__handle:hover { background: var(--ad-hover); color: var(--ad-text); }
    .brand-order__n { min-width: 18px; font-variant-numeric: tabular-nums; }
    tbody[data-reorder-url] tr[data-id] { cursor: grab; }
    tbody[data-reorder-url] tr[data-id] a, tbody[data-reorder-url] tr[data-id] button:not([data-brand-handle]) { cursor: pointer; }
    tr.is-dragging td { background: var(--ad-brand-soft) !important; }
    tr.is-dragging { box-shadow: 0 6px 18px rgba(0, 0, 0, .25); position: relative; z-index: 2; }
    tr.is-dragging .brand-order__handle { cursor: grabbing; color: var(--ad-brand-text); border-color: var(--ad-brand-text); }
    body.brand-dragging, body.brand-dragging * { cursor: grabbing !important; user-select: none; }
</style>
@endpush

@push('scripts')
<script>
    // Brand order: drag a row from anywhere on it with the mouse (its Edit and Delete buttons still just
    // click), by its handle with a finger, or focus the handle and press the arrow keys. The rows move as you go; the new order is saved when you let go and the numbers are redone.
    (function () {
        var drag = null, keyTimer = null;

        function body() { return document.querySelector('tbody[data-reorder-url]'); }
        function rows() { return Array.prototype.slice.call(body().querySelectorAll('tr[data-id]')); }
        function ids() { return rows().map(function (row) { return row.getAttribute('data-id'); }); }

        function save(before) {
            var now = ids();
            if (before && before.join() === now.join()) {
                return;
            }
            rows().forEach(function (row, i) { row.querySelector('.brand-order__n').textContent = i + 1; });
            $.ajax({url: body().getAttribute('data-reorder-url'), type: 'POST', dataType: 'json',
                    data: {_token: '{{ csrf_token() }}', ids: now}, headers: {Accept: 'application/json'}})
                .done(function (response) { AdminUI.toast((response && response.message) || 'Brand order saved.'); })
                .fail(function (xhr) {
                    AdminUI.toast((xhr.responseJSON && xhr.responseJSON.message) || 'The order could not be saved. The list was reloaded.', 'error');
                    AdminUI.refresh();
                });
        }

        // A press anywhere on a row (not on its links and buttons) becomes a drag once the pointer has moved
        // a few pixels, so a plain click still does nothing unexpected
        $(document).on('pointerdown', 'tbody[data-reorder-url] tr[data-id]', function (e) {
            if ((e.button !== undefined && e.button !== 0) || $(e.target).closest('a, button:not([data-brand-handle]), input, select, textarea').length) {
                return;
            }
            // on a touch screen a finger on the row scrolls the page; the handle drags
            if (e.pointerType === 'touch' && !$(e.target).closest('[data-brand-handle]').length) {
                return;
            }
            drag = {row: this, before: ids(), startY: e.clientY, moving: false};
            e.preventDefault();
        });

        // The row is moved in the page as the pointer goes, so moves and the release are followed on the whole document
        document.addEventListener('pointermove', function (e) {
            if (!drag) {
                return;
            }
            if (!drag.moving) {
                if (Math.abs(e.clientY - drag.startY) < 5) {
                    return;
                }
                drag.moving = true;
                drag.row.classList.add('is-dragging');
                document.body.classList.add('brand-dragging');
            }
            var target = null;
            rows().some(function (row) {
                if (row === drag.row) {
                    return false;
                }
                var r = row.getBoundingClientRect();
                if (e.clientY < r.top + r.height / 2) {
                    target = row;
                    return true;
                }
                return false;
            });
            if (target) {
                if (target !== drag.row.nextElementSibling) {
                    body().insertBefore(drag.row, target);
                }
            } else if (drag.row !== body().lastElementChild) {
                body().appendChild(drag.row);
            }
            // near the top or bottom of the window the page scrolls along
            if (e.clientY < 70) {
                window.scrollBy(0, -14);
            } else if (e.clientY > window.innerHeight - 70) {
                window.scrollBy(0, 14);
            }
        });

        function drop() {
            if (!drag) {
                return;
            }
            drag.row.classList.remove('is-dragging');
            document.body.classList.remove('brand-dragging');
            var before = drag.before, moved = drag.moving;
            drag = null;
            if (moved) {
                save(before);
            }
        }
        document.addEventListener('pointerup', drop);
        document.addEventListener('pointercancel', drop);

        $(document).on('keydown', '[data-brand-handle]', function (e) {
            if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') {
                return;
            }
            e.preventDefault();
            var row = this.closest('tr');
            var before = keyTimer ? null : ids();
            if (e.key === 'ArrowUp' && row.previousElementSibling) {
                body().insertBefore(row, row.previousElementSibling);
            } else if (e.key === 'ArrowDown' && row.nextElementSibling) {
                body().insertBefore(row.nextElementSibling, row);
            }
            this.focus();
            // a few presses in a row are saved once
            clearTimeout(keyTimer);
            keyTimer = setTimeout(function () { keyTimer = null; save(before); }, 600);
        });
    })();

    $(document).on('click', '[data-delete-brand]', function () {
        var products = parseInt($(this).data('products'), 10);
        var question = 'Delete the brand "' + $(this).data('name') + '"?' +
            (products ? '\n\nIts ' + products + ' product(s) stay in the shop, just without a brand.' : '');
        if (!confirm(question)) {
            return;
        }
        $.ajax({type: 'DELETE', url: $(this).data('delete-brand'), data: {_token: '{{ csrf_token() }}'}})
            .done(function (response) { AdminUI.toast((response && response.message) || 'Brand deleted.'); AdminUI.refresh(); })
            .fail(function (xhr) { AdminUI.toast((xhr.responseJSON && xhr.responseJSON.message) || 'Could not delete the brand.', 'error'); });
    });
</script>
@endpush
