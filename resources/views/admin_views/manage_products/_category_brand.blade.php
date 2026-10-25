{{-- Category (main categories with their subcategories under them) and brand, on the add and edit product forms --}}
@php
    $chosenCategory = old('category_id', $product->category_id ?? null);
    $chosenBrand = old('brand_id', $product->brand_id ?? null);
@endphp
<div class="col-lg-6 col-12 mb-30">
    <label for="category_id">Category</label>
    <select name="category_id" id="category_id" class="form-control select2">
        @foreach(\App\Models\Category::tree() as $main)
            @if($main->children->isEmpty())
                <option value="{{ $main->id }}" @selected($chosenCategory == $main->id)>{{ $main->name }}</option>
            @else
                <optgroup label="{{ $main->name }}">
                    <option value="{{ $main->id }}" @selected($chosenCategory == $main->id)>{{ $main->name }} (no subcategory)</option>
                    @foreach($main->children as $sub)
                        <option value="{{ $sub->id }}" @selected($chosenCategory == $sub->id)>{{ $main->name }} › {{ $sub->name }}</option>
                    @endforeach
                </optgroup>
            @endif
        @endforeach
    </select>
    @if ($errors->has('category_id'))
        <span class="text-danger">{{ $errors->first('category_id') }}</span>
    @endif
</div>
<div class="col-lg-6 col-12 mb-30">
    <label for="brand_id">Brand <small class="text-muted">(optional)</small></label>
    <select name="brand_id" id="brand_id" class="form-control select2">
        <option value="">No brand</option>
        @foreach(\App\Models\Brand::cachedAll()->sortBy('name') as $brand)
            <option value="{{ $brand->id }}" @selected($chosenBrand == $brand->id)>{{ $brand->name }}</option>
        @endforeach
    </select>
</div>
