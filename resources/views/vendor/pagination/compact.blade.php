@if ($paginator->hasPages())
<style>
.pg-wrap { display:flex; flex-direction:column; align-items:center; gap:.5rem; }
.pg-list { display:flex; flex-wrap:wrap; justify-content:center; gap:.25rem; list-style:none; margin:0; padding:0; }
.pg-list a, .pg-list span.pg-item {
    display:inline-flex; align-items:center; justify-content:center;
    min-width:2rem; height:2rem; padding:0 .55rem;
    font-size:.82rem; font-weight:600; line-height:1;
    border:1px solid var(--border, #e5e7eb); border-radius:8px;
    background:#fff; color:var(--text, #1f2937); text-decoration:none;
    transition:background .15s, color .15s, border-color .15s;
}
.pg-list a:hover { border-color:var(--primary, #dc2626); color:var(--primary, #dc2626); }
.pg-list .pg-active span.pg-item { background:var(--primary, #dc2626); border-color:var(--primary, #dc2626); color:#fff; }
.pg-list .pg-disabled span.pg-item { opacity:.45; cursor:not-allowed; }
.pg-summary { font-size:.78rem; color:var(--muted, #6b7280); margin:0; }
</style>
<nav class="pg-wrap" role="navigation" aria-label="Pagination Navigation">
    <ul class="pg-list">
        @if ($paginator->onFirstPage())
            <li class="pg-disabled" aria-disabled="true"><span class="pg-item">&lsaquo;</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">&lsaquo;</a></li>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="pg-disabled" aria-disabled="true"><span class="pg-item">{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="pg-active" aria-current="page"><span class="pg-item">{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">&rsaquo;</a></li>
        @else
            <li class="pg-disabled" aria-disabled="true"><span class="pg-item">&rsaquo;</span></li>
        @endif
    </ul>
    <p class="pg-summary">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} results</p>
</nav>
@endif
