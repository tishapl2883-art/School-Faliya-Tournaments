@if ($paginator->hasPages())
    <nav class="pager">
        @if (! $paginator->onFirstPage())<a class="btn small" href="{{ $paginator->previousPageUrl() }}">&laquo; Previous</a>@endif
        <span>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())<a class="btn small" href="{{ $paginator->nextPageUrl() }}">Next &raquo;</a>@endif
    </nav>
@endif
