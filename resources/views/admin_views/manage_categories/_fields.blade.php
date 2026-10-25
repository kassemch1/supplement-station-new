{{-- Where the category sits (main category or inside one), its icon, an optional picture and its order.
     Used by the add and edit category forms. --}}
@php
    use App\Support\Icons;
    $current = $category ?? null;
    $hasChildren = $current && $current->exists && \App\Models\Category::where('parent_id', $current->id)->exists();
@endphp
<div class="col-lg-6 col-12 mb-30">
    <label for="parent_id">Place</label>
    @if($hasChildren)
        <input type="hidden" name="parent_id" value="">
        <input class="form-control" type="text" value="Main category (it has subcategories)" disabled>
    @else
        <select name="parent_id" id="parent_id" class="form-control">
            <option value="">Main category (shown with an icon at the top of the shop)</option>
            @foreach($mainCategories as $main)
                @continue($current && $main->id === $current->id)
                <option value="{{ $main->id }}" @selected($current && (int) $current->parent_id === $main->id)>Inside {{ $main->name }}</option>
            @endforeach
        </select>
    @endif
</div>
<div class="col-lg-6 col-12 mb-30">
    <label for="sort_order">Order <small class="text-muted">(lower first)</small></label>
    <input name="sort_order" id="sort_order" class="form-control" type="number" min="0" value="{{ $current->sort_order ?? 0 }}">
</div>
<div class="col-12 mb-30">
    <label>Icon</label>
    <div class="ss-iconpick">
        <label class="ss-iconpick__item">
            <input type="radio" name="icon" value="" @checked(!$current || !$current->icon)>
            <span class="ss-iconpick__tile">—</span><span class="ss-iconpick__name">None</span>
        </label>
        @foreach(Icons::options() as $key => $label)
            <label class="ss-iconpick__item" title="{{ $label }}">
                <input type="radio" name="icon" value="{{ $key }}" @checked($current && $current->icon === $key)>
                <span class="ss-iconpick__tile">{!! Icons::svg($key, 'ss-iconpick__svg') !!}</span><span class="ss-iconpick__name">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>
<div class="col-lg-6 col-12 mb-30">
    <label>Picture <small class="text-muted">(optional, shown instead of the icon; saved as a small WebP)</small></label>
    @if($current && $current->exists && $current->imageUrl())
        @include('admin_views.partials.removable_image', [
            'src' => $current->imageUrl(), 'url' => route('manageCategories.removeImage', $current->id),
            'confirm' => 'Remove this category\'s picture? Its icon will show instead.',
        ])
    @endif
    <input class="dropify" type="file" name="image" accept="image/png,image/jpeg,image/webp">
</div>

@once
    @push('styles')
    <style>
        .ss-iconpick { display: grid; grid-template-columns: repeat(auto-fill, minmax(92px, 1fr)); gap: 8px; max-height: 320px; overflow-y: auto; padding: 4px; }
        .ss-iconpick__item { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 10px 4px; border-radius: 12px; border: 1.5px solid rgba(127,127,127,.25); cursor: pointer; margin: 0; text-align: center; }
        .ss-iconpick__item input { position: absolute; opacity: 0; pointer-events: none; }
        .ss-iconpick__tile { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 12px; background: rgba(160,35,52,.1); color: #c8304a; font-weight: 700; }
        .ss-iconpick__svg { width: 24px; height: 24px; }
        .ss-iconpick__name { font-size: 11px; line-height: 1.2; opacity: .8; }
        .ss-iconpick__item:has(input:checked) { border-color: #c8304a; background: rgba(160,35,52,.08); }
        .ss-iconpick__item:has(input:checked) .ss-iconpick__tile { background: #a02334; color: #fff; }
    </style>
    @endpush
@endonce
