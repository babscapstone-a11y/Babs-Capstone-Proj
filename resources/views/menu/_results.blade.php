@if($menuItems->total())
<div class="results-count">{{ $menuItems->total() }} item{{ $menuItems->total() === 1 ? '' : 's' }} found</div>
@endif

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:56px">Image</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Type</th>
                <th>Price</th>
                <th>Availability</th>
                <th>Status</th>
                <th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($menuItems as $item)
            <tr>
                <td>
                    @if($item->image && Storage::disk('public')->exists($item->image))
                        <img src="{{ Storage::url($item->image) }}" class="menu-thumb" alt="{{ $item->menu_name }}">
                    @else
                        <div class="menu-thumb-placeholder"><i class="fas fa-image"></i></div>
                    @endif
                </td>
                <td>
                    <div class="item-name-cell">
                        <div>
                            <div class="item-name-val">{{ $item->menu_name }}</div>
                            @if($item->description)
                                <div class="item-cat-val">{{ Str::limit($item->description, 40) }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td>{{ $item->category?->category_name ?? '—' }}</td>
                <td>
                    @if($item->item_type === 'food')
                        <span class="badge badge-type-food"><i class="fas fa-bowl-food"></i> Food</span>
                    @else
                        <span class="badge badge-type-beverage"><i class="fas fa-glass-water"></i> Beverage</span>
                    @endif
                </td>
                <td class="price-val">₱{{ number_format($item->price, 2) }}</td>
                <td>
                    @if($item->is_available)
                        <span class="badge badge-avail-yes"><i class="fas fa-circle" style="font-size:.45rem"></i> Available</span>
                    @else
                        <span class="badge badge-avail-no"><i class="fas fa-circle" style="font-size:.45rem"></i> Unavailable</span>
                    @endif
                </td>
                <td>
                    @if($item->is_active)
                        <span class="badge badge-active"><i class="fas fa-circle" style="font-size:.45rem"></i> Active</span>
                    @else
                        <span class="badge badge-inactive"><i class="fas fa-circle" style="font-size:.45rem"></i> Inactive</span>
                    @endif
                </td>
                <td>
                    <div class="row-acts" style="justify-content:flex-end">
                        <a href="{{ route('menu.show', $item) }}" class="act-btn act-view" title="View" aria-label="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('update', $item)
                        <a href="{{ route('menu.edit', $item) }}" class="act-btn act-edit" title="Edit" aria-label="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        @endcan
                        @can('toggleStatus', $item)
                        <button type="button"
                            class="act-btn {{ $item->is_active ? 'act-danger' : 'act-success' }}"
                            title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}" aria-label="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"
                            onclick="openToggleModal({{ $item->id }}, '{{ addslashes($item->menu_name) }}', {{ $item->is_active ? 'true' : 'false' }})">
                            <i class="fas fa-{{ $item->is_active ? 'ban' : 'circle-check' }}"></i>
                        </button>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <i class="fas fa-utensils" style="color:var(--muted)"></i>
                        <h3>No Menu Items Found</h3>
                        <p>{{ request()->hasAny(['search','category_id','item_type','is_active','is_available']) ? 'No matching records found.' : 'Start by adding your first menu item.' }}</p>
                        @can('create', App\Models\MenuItem::class)
                        <a href="{{ route('menu.create') }}" style="display:inline-flex;align-items:center;gap:.4rem;background:linear-gradient(90deg,var(--primary),#F97316);color:#fff;padding:.55rem 1.2rem;border-radius:10px;font-size:.83rem;font-weight:700;text-decoration:none">
                            <i class="fas fa-plus"></i> Add First Menu Item
                        </a>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($menuItems->hasPages())
<div style="padding:.85rem 1.2rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
    <span style="font-size:.78rem;color:var(--muted)">
        Showing {{ $menuItems->firstItem() }}–{{ $menuItems->lastItem() }} of {{ $menuItems->total() }} items
    </span>
    {{ $menuItems->links() }}
</div>
@endif
