@php
    $stockStatus = $item->isRtcTracked() ? $item->rtc_servings_status : null;
    $availableStock = $item->available_stock;
    $soldOut = $stockStatus === 'out_of_stock';
    $selectable = $item->is_available && ! $soldOut;
@endphp
<article class="menu-card {{ $selectable ? '' : 'menu-card-disabled' }}"
    @if($selectable)
    onclick="openItemModal(
        {{ $item->id }},
        {{ Js::from($item->menu_name) }},
        {{ Js::from($item->description ?? '') }},
        {{ $item->price }},
        {{ Js::from($item->category?->category_name ?? 'Uncategorized') }},
        {{ Js::from($item->item_type) }},
        {{ Js::from($item->image_url) }},
        {{ Js::from($availableStock) }}
    )"
    role="button" tabindex="0"
    aria-label="Select {{ $item->menu_name }}"
    onkeypress="if(event.key==='Enter') this.click();"
    @endif
>
    <div class="card-img">
        @if($item->image)
            <img src="{{ $item->image_url }}" alt="{{ $item->menu_name }}" loading="lazy">
        @else
            <div class="card-img-placeholder">
                <i class="fas {{ $item->isBeverage() ? 'fa-mug-hot' : 'fa-bowl-food' }}"></i>
            </div>
        @endif
        <span class="card-type-badge {{ $item->isBeverage() ? 'badge-beverage' : 'badge-food' }}">
            {{ $item->isBeverage() ? 'Beverage' : 'Food' }}
        </span>
        @if(! $item->is_available)
        <div class="card-unavailable-overlay">
            <span class="badge badge-avail-no"><i class="fas fa-circle" style="font-size:.45rem"></i> Currently Unavailable</span>
        </div>
        @elseif($soldOut)
        <div class="card-unavailable-overlay">
            <span class="badge badge-avail-no"><i class="fas fa-circle" style="font-size:.45rem"></i> Sold Out</span>
        </div>
        @elseif($stockStatus === 'low_stock')
        <span class="card-stock-badge stock-low">Low Stock</span>
        @endif
    </div>
    <div class="card-body">
        <div class="card-category">{{ $item->category?->category_name ?? 'Uncategorized' }}</div>
        <h3 class="card-name">{{ $item->menu_name }}</h3>
        @if($item->description)
        <p class="card-desc">{{ $item->description }}</p>
        @endif
        <div class="card-footer">
            <span class="card-price">₱{{ number_format($item->price, 2) }}</span>
            @if($selectable)
            <button
                class="add-btn"
                onclick="event.stopPropagation(); openItemModal(
                    {{ $item->id }},
                    {{ Js::from($item->menu_name) }},
                    {{ Js::from($item->description ?? '') }},
                    {{ $item->price }},
                    {{ Js::from($item->category?->category_name ?? 'Uncategorized') }},
                    {{ Js::from($item->item_type) }},
                    {{ Js::from($item->image_url) }},
                    {{ Js::from($availableStock) }}
                )"
                aria-label="Select {{ $item->menu_name }}"
                title="Select item"
            >
                <i class="fas fa-plus"></i>
            </button>
            @else
            <button class="add-btn add-btn-disabled" disabled title="{{ ! $item->is_available ? 'Currently unavailable' : 'Sold out' }}">
                <i class="fas fa-ban"></i>
            </button>
            @endif
        </div>
    </div>
</article>
