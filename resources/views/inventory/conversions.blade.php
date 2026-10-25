@extends('layouts.admin')
@section('title', 'RTC Conversion History')

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
.filter-date{padding:.55rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.83rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface)}
.btn{display:inline-flex;align-items:center;gap:.45rem;padding:.55rem 1.1rem;border-radius:10px;font-size:.83rem;font-weight:600;font-family:inherit;cursor:pointer;border:none;transition:all .18s;text-decoration:none}
.btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:#B91C1C}
.btn-outline{background:var(--surface);border:1.5px solid var(--border);color:var(--dark)}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
.btn-blue{background:#2563EB;color:#fff}.btn-blue:hover{background:#1D4ED8}
.btn-sm{padding:.38rem .75rem;font-size:.78rem}
.card{background:var(--surface);border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.07);overflow:hidden}
.table-wrap{overflow-x:auto}
.inv-table{width:100%;border-collapse:collapse;font-size:.83rem}
.inv-table th{padding:.65rem 1rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);background:var(--surface-2);border-bottom:1px solid var(--border)}
.inv-table td{padding:.8rem 1rem;border-bottom:1px solid var(--line);color:var(--dark);vertical-align:middle}
.inv-table tr:last-child td{border-bottom:none}
.inv-table tr:hover td{background:var(--surface-2)}
.empty-row td{text-align:center;color:var(--muted);padding:2rem;font-size:.84rem}
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:center;justify-content:center;padding:1rem}
.modal-backdrop.open{display:flex}
.modal{background:var(--surface);border-radius:20px;width:100%;max-width:500px;box-shadow:0 24px 64px rgba(0,0,0,.18);animation:slideUp .25s cubic-bezier(.34,1.56,.64,1) both}
@keyframes slideUp{from{opacity:0;transform:scale(.9) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
.modal-hd{display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border)}
.modal-hd h3{font-size:1rem;font-weight:800;color:var(--dark);display:flex;align-items:center;gap:.5rem}
.modal-hd h3 i{color:var(--blue-600)}
.modal-close-btn{width:32px;height:32px;border-radius:8px;border:none;background:var(--surface-2);cursor:pointer;font-size:.9rem;color:var(--muted);display:flex;align-items:center;justify-content:center}
.modal-close-btn:hover{background:var(--red-100);color:var(--primary)}
.modal-body{padding:1.5rem}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.6rem;justify-content:flex-end}
.field{margin-bottom:1.1rem}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--dark);margin-bottom:.35rem}
.field input,.field select,.field textarea{width:100%;padding:.6rem .9rem;border:1.5px solid var(--border);border-radius:10px;font-size:.84rem;font-family:inherit;color:var(--dark);outline:none;background:var(--surface);transition:border-color .18s}
.field input:focus,.field select:focus,.field textarea:focus{border-color:#2563EB}
.error-msg{background:var(--red-50);border:1.5px solid var(--red-200);color:var(--red-700);border-radius:10px;padding:.7rem 1rem;font-size:.82rem;margin-bottom:1.1rem}
.conv-calc{background:var(--blue-50);border-radius:12px;padding:1rem 1.2rem;margin-top:.75rem;border:1px solid var(--blue-200)}
.conv-calc .calc-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--blue-700);margin-bottom:.4rem}
.conv-result{font-size:1.5rem;font-weight:900;color:var(--blue-700)}
.conv-flow{display:flex;align-items:center;gap:.75rem;margin-top:.6rem;flex-wrap:wrap}
.flow-box{background:var(--surface);border:1px solid var(--blue-200);border-radius:10px;padding:.5rem .85rem;text-align:center}
.flow-box .fl-val{font-size:.92rem;font-weight:700;color:var(--blue-700)}
.flow-box .fl-lbl{font-size:.7rem;color:#93C5FD;font-weight:600}
.flow-arrow{color:#93C5FD;font-size:1rem}
</style>
@endsection

@section('content')
<div class="inv-page">
    <div class="page-header">
        <div>
            <div class="page-title"><i class="fas fa-arrows-rotate"></i> RTC Conversion History</div>
            <div class="page-sub">Raw meat → RTC serving conversion records</div>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <button class="btn btn-blue" onclick="cvOpen()"><i class="fas fa-arrows-rotate"></i> New Conversion</button>
        </div>
    </div>

    @if(session('success'))
    <div style="background:var(--green-50);border:1.5px solid var(--green-300);border-radius:12px;padding:.85rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.65rem;font-size:.85rem;color:var(--green-800);font-weight:500;"><i class="fas fa-check-circle" style="color:var(--green-600);"></i> {{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('inventory.conversions.index') }}" class="filter-bar">
        <div class="search-wrap"><i class="fas fa-search"></i><input type="text" name="q" value="{{ request('q') }}" placeholder="Search raw item or menu item…" class="search-input"></div>
        @include('partials.date-range-filter')
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
        @if(request()->hasAny(['q','from','to']))
        <a href="{{ route('inventory.conversions.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Clear</a>
        @endif
    </form>

    <div class="card">
        <div class="table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>Raw Item</th>
                        <th>Menu Item</th>
                        <th>Raw Used</th>
                        <th>Portion Size</th>
                        <th>RTC Produced</th>
                        <th>Prev Raw Stock</th>
                        <th>Remaining Raw</th>
                        <th>Prev Servings</th>
                        <th>New Servings</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- One row per conversion; a dish made from several raw meats lists each meat on its own line --}}
                    @forelse($conversions as $c)
                    <tr>
                        <td>@foreach($c->lines as $l)<div style="font-weight:700">{{ $l->inventoryItem?->item_name ?? '—' }}</div>@endforeach</td>
                        <td><div style="font-weight:700;color:var(--blue-700)">{{ $c->menuItem?->menu_name ?? '—' }}</div></td>
                        <td style="color:var(--red-600);font-weight:700">@foreach($c->lines as $l)<div>{{ number_format($l->raw_quantity_used, 2) }} @unit($l->unit)</div>@endforeach</td>
                        <td style="color:var(--muted)">@foreach($c->lines as $l)<div>{{ $l->portion_label }}/srv</div>@endforeach</td>
                        <td><span style="font-weight:800;color:var(--blue-700);font-size:.95rem">{{ number_format($c->servings_produced,0) }}</span> <span style="font-size:.75rem;color:var(--muted)">srv</span></td>
                        <td style="color:var(--muted)">@foreach($c->lines as $l)<div>{{ number_format($l->previous_raw_stock, 2) }} @unit($l->unit)</div>@endforeach</td>
                        <td style="font-weight:600">@foreach($c->lines as $l)<div>{{ number_format($l->remaining_raw_stock, 2) }} @unit($l->unit)</div>@endforeach</td>
                        <td style="color:var(--muted)">{{ number_format($c->previous_servings,0) }}</td>
                        <td style="font-weight:700;color:var(--green-600)">{{ number_format($c->new_servings,0) }}</td>
                        <td style="white-space:nowrap">{{ $c->created_at->format('M d, Y') }}</td>
                        <td style="white-space:nowrap">{{ $c->created_at->format('h:i A') }}</td>
                        <td>{{ $c->converter?->name ?? 'Admin' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="empty-row"><i class="fas fa-arrows-rotate" style="font-size:1.4rem;margin-bottom:.5rem;display:block;opacity:.4"></i>No conversions recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($conversions->hasPages())
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--border)">{{ $conversions->links() }}</div>
        @endif
    </div>
</div>

{{-- Convert Raw Meat → RTC Servings (shared form) --}}
@include('inventory.partials.convert-modal', ['menuItems' => $menuItems, 'rawItems' => $rtcItems, 'submitClass' => 'btn-blue'])
@endsection

@section('scripts')
<script>
function openLocalModal(id){document.getElementById(id).classList.add('open');document.body.style.overflow='hidden';}
function closeLocalModal(id){document.getElementById(id).classList.remove('open');document.body.style.overflow='';}
document.querySelectorAll('.modal-backdrop').forEach(el=>el.addEventListener('click',e=>{if(e.target===el)closeLocalModal(el.id);}));
</script>
@endsection
