{{-- Shop heading, category tiles, subcategories and (on phones) brands. Sent again by the shop script
     when the customer moves to another category. --}}
@php
    use App\Support\Icons;
    use App\Support\ShopUrl;
    $activeMainId = $main?->id;
    $subs = $main ? ($tree->firstWhere('id', $main->id)?->children ?? collect()) : collect();
    $chosenBrands = $filters['brands']->pluck('slug')->all();
@endphp
<div class="ss-shop-top">
    <div class="ss-wrap ss-shop-title">
        <nav class="ss-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span>/</span>
            <a href="{{ route('shop') }}" data-shop-link>Shop</a>
            @if($main && $main->id !== $category?->id)
                <span>/</span><a href="{{ $main->url() }}" data-shop-link>{{ $main->name }}</a>
            @endif
            @if($category)
                <span>/</span><span aria-current="page">{{ $category->name }}</span>
            @endif
        </nav>
        <h1 class="ss-shop-title__h">{{ $heading }}</h1>
    </div>

    <div class="ss-cats" role="navigation" aria-label="Categories">
        <div class="ss-cats__track ss-wrap">
            <a href="{{ route('shop') }}" class="ss-cat {{ $category ? '' : 'is-active' }}" data-shop-link style="--hue: 350">
                <span class="ss-cat__tile">{!! Icons::svg('all', 'ss-cat__icon') !!}</span>
                <span class="ss-cat__label">All</span>
            </a>
            @foreach($tree as $index => $item)
                <a href="{{ $item->url() }}" class="ss-cat {{ $activeMainId === $item->id ? 'is-active' : '' }}" data-shop-link
                   style="--hue: {{ [8, 205, 265, 42, 160, 18, 330, 190, 28, 220, 0][$index % 11] }}">
                    <span class="ss-cat__tile">
                        @if($item->imageUrl())
                            <img src="{{ $item->imageUrl() }}" alt="" width="40" height="40" loading="lazy">
                        @else
                            {!! $item->iconSvg('ss-cat__icon') !!}
                        @endif
                    </span>
                    <span class="ss-cat__label">{{ $item->name }}</span>
                </a>
            @endforeach
        </div>
    </div>

    @if($subs->isNotEmpty())
        <div class="ss-subs" aria-label="{{ $main->name }} types">
            <div class="ss-subs__track ss-wrap">
                <a href="{{ $main->url() }}" class="ss-sub {{ $category?->id === $main->id ? 'is-active' : '' }}" data-shop-link>
                    <span class="ss-sub__icon">{!! $main->iconSvg('ss-sub__svg') !!}</span>
                    All {{ $main->name }}
                    <span class="ss-count">{{ $counts[$main->id] ?? 0 }}</span>
                </a>
                @foreach($subs as $sub)
                    <a href="{{ $sub->url() }}" class="ss-sub {{ $category?->id === $sub->id ? 'is-active' : '' }}" data-shop-link>
                        <span class="ss-sub__icon">
                            @if($sub->imageUrl())
                                <img src="{{ $sub->imageUrl() }}" alt="" width="22" height="22" loading="lazy">
                            @else
                                {!! $sub->iconSvg('ss-sub__svg') !!}
                            @endif
                        </span>
                        {{ $sub->name }}
                        <span class="ss-count">{{ $counts[$sub->id] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($brands->isNotEmpty())
        <div class="ss-brandbar" aria-label="Brands">
            <div class="ss-brandbar__track ss-wrap">
                @foreach($brands as $brand)
                    <a href="{{ ShopUrl::toggleBrand($baseUrl, $brand->slug) }}" data-shop-link
                       class="ss-brandchip {{ in_array($brand->slug, $chosenBrands, true) ? 'is-active' : '' }}">
                        <span class="ss-brandchip__logo">
                            @if($brand->logoUrl())
                                <img src="{{ $brand->logoUrl() }}" alt="" loading="lazy">
                            @else
                                <span class="ss-mono">{{ $brand->initials() }}</span>
                            @endif
                        </span>
                        <span class="ss-brandchip__name">{{ $brand->name }}</span>
                        <span class="ss-count">{{ $brandCounts[$brand->id] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
