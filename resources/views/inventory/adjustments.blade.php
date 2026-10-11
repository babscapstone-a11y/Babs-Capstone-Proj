@extends('layouts.admin')
@section('title', 'Inventory Adjustments')

@section('styles')
<style>
.inv-page{padding:2rem;max-width:1400px;margin:0 auto}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:2rem;flex-wrap:wrap}
.page-title{font-size:1.5rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.65rem}
.page-title i{color:var(--primary)}
.page-sub{font-size:.83rem;color:var(--muted);margin-top:.25rem}
.filter-bar{display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;margin-bottom:1.25rem}
.search-wrap{position:relative;flex:1;min-width:220px}
.search-wrap i{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.85rem;pointer-events:none}
.search-input{width:100%;padding:.55rem .9rem .55rem 2.3rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface)}
.search-input:focus{border-color:var(--primary)}
.filter-select,.filter-date{padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface)}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:var(--surface);border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-purple{background:#7C3AED;color:#fff}.btn-purple:hover{background:#6D28D9}
.btn-sm{padding:.38rem .75rem;font-size:.78rem}
.card{background:var(--surface);border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:var(--surface-2);border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid var(--line);color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr:hover td{background:var(--surface-2)}
.badge{display:inline-flex;align-items:center;gap:.3rem;padding:.22rem .65rem;border-radius:50px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.badge-damaged{background:var(--red-50);color:var(--red-700)}.badge-expired{background:var(--amber-100);color:var(--amber-800)}.badge-missing{background:var(--violet-50);color:var(--violet-700)}.badge-correction{background:var(--green-50);color:var(--green-700)}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex}
.modal{background:var(--surface);border-radius:20px;width:100%;max-width:480px;box-shadow:0 24px 64px rgba(0,0,0,.18);animation:slideUp .25s cubic-bezier(.34,1.56,.64,1) both}
@keyframes slideUp{from{opacity:0;transform:scale(.9) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-hd{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)}
.modal-hd h3{font-size:1rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.5rem}
.modal-close-btn{width:32px;height:32px;border-radius:8px;border:none;background:var(--surface-2);cursor:pointer;font-size:.9rem;color:var(--muted);display:flex;align-items:center;justify-content:center}
.modal-close-btn:hover{background:var(--red-100);color:var(--primary)}
.modal-body{padding:1.5rem}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.6rem;justify-content:flex-end}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--dark);margin-bottom:.35rem}
.field input,.field select,.field textarea{width:100%;padding:.6rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface);transition:border-color .18s}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--primary)}
.adj-type-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}
.adj-type-btn{padding:.55rem .8rem;border-radius:10px;border:1.5px solid var(--border);background:var(--surface);cursor:pointer;font-size:.81rem;font-weight:600;font-family:inherit;color:var(--dark);transition:all .15s;text-align:center}
.adj-type-btn.selected{border-color:var(--primary);background:var(--red-50);color:var(--primary)}
.preview-box{background:var(--surface-2);border-radius:12px;padding:1rem 1.2rem;margin:.75rem 0;border:1px solid var(--border)}
.preview-row{display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:.3rem}
.preview-row:last-child{margin-bottom:0;font-weight:700;color:var(--primary)}
</style>
@endsection

@section('content')
<div class="inv-page">
    <div class="page-header">
        <div>
            <div class="page-title"><i class="fas fa-pen-to-square"></i> Inventory Adjustments</div>
            <div class="page-sub">Manual corrections, damaged, expired, and missing stock records</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
            <button class="btn btn-purple" onclick="openAdjust()"><i class="fas fa-plus"></i> New Adjustment</button>
        </div>
    </div>

    @if(session('success'))
    <div style="background:var(--green-50);border:1.5px solid var(--green-300);border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.65rem;font-size:.85rem;color:var(--green-800);font-weight:500;"><i class="fas fa-check-circle" style="color:var(--green-600);"></i> {{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('inventory.adjustments.index') }}" class="filter-bar">
        <div class="search-wrap"><i class="fas fa-search"></i><input type="text" name="q" value="{{ request('q') }}" placeholder="Search item…" class="search-input"></div>
        <select name="type" class="filter-select" onchange="this.form.submit()">
            <option value="">All Reasons</option>
            <option value="damaged"    {{ request('type')==='damaged'    ? 'selected' : '' }}>Damaged</option>
            <option value="expired"    {{ request('type')==='expired'    ? 'selected' : '' }}>Expired</option>
            <option value="missing"    {{ request('type')==='missing'    ? 'selected' : '' }}>Missing</option>
            <option value="correction" {{ request('type')==='correction' ? 'selected' : '' }}>Correction</option>
        </select>
        @include('partials.date-range-filter')
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        @if(request()->hasAny(['q','type','from','to']))
        <a href="{{ route('inventory.adjustments.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
        @endif
    </form>

    <div class="card">
        <div class="table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Reason</th>
                        <th>Before</th>
                        <th>Adjustment</th>
                        <th>After</th>
                        <th>Remarks</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Adjusted By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    @php
                        $typeClass = match($adj->adjustment_type) {
                            'damaged'    => 'badge-damaged',
                            'expired'    => 'badge-expired',
                            'missing'    => 'badge-missing',
                            'correction' => 'badge-correction',
                            default      => ''
                        };
                        $isNeg = (float)$adj->quantity_adjusted < 0;
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:700">{{ $adj->subject_name }}</div>
                            <div style="font-size:.72rem;color:var(--muted)">{{ $adj->subject_kind }}</div>
                        </td>
                        <td><span class="badge {{ $typeClass }}">{{ $adj->getTypeLabel() }}</span></td>
                        <td style="color:var(--muted)">{{ number_format($adj->quantity_before,2) }} {{ $adj->unit_label }}</td>
                        <td style="font-weight:700;color:{{ $isNeg ? '#DC2626' : '#16A34A' }}">
                            {{ $isNeg ? '' : '+' }}{{ number_format($adj->quantity_adjusted,2) }} {{ $adj->unit_label }}
                        </td>
                        <td style="font-weight:700">{{ number_format($adj->quantity_after,2) }} {{ $adj->unit_label }}</td>
                        <td style="font-size:.8rem;color:var(--muted)">{{ $adj->remarks ?: ($adj->reason !== $adj->getTypeLabel() ? $adj->reason : '—') }}</td>
                        <td style="white-space:nowrap">{{ $adj->created_at->format('M d, Y') }}</td>
                        <td style="white-space:nowrap">{{ $adj->created_at->format('h:i A') }}</td>
                        <td>{{ $adj->adjuster?->name ?? 'Admin' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="empty-row"><i class="fas fa-pen-to-square" style="font-size:1.4rem;margin-bottom:.5rem;display:block;opacity:.4"></i>No adjustments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($adjustments->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--border)">{{ $adjustments->links() }}</div>
        @endif
    </div>
</div>

@include('inventory.partials.adjust-modal', [
    'field' => 'inventory_item_id', 'itemLabel' => 'Inventory Item',
    'options' => $rtcItems->concat($beverageItems)->map(fn ($i) => ['id' => $i->id, 'name' => $i->item_name, 'qty' => (float) $i->quantity, 'unit' => \App\Support\Units::short($i->unit)])->all(),
    'decimals' => 2, 'searchHint' => 'Search inventory items…',
])
@endsection

@section('scripts')
<script>
function openLocalModal(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closeLocalModal(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
document.querySelectorAll('.modal-backdrop').forEach(el=>el.addEventListener('click',e=>{if(e.target===el)closeLocalModal(el.id);}));
</script>
@endsection
