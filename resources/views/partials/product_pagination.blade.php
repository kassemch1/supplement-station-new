<div class="d-flex justify-content-center" id="pagination-container">
    <nav aria-label="Page navigation">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($product->onFirstPage())
                <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $product->appends(request()->query())->previousPageUrl() }}">&laquo;</a>
                </li>
            @endif

            {{-- Pagination Logic --}}
            @php
                $currentPage = $product->currentPage();
                $lastPage = $product->lastPage();
                $linksToShow = 5; // Total links to show
                $displayedPages = [];

                if ($lastPage <= $linksToShow) {
                    $displayedPages = range(1, $lastPage);
                } else {
                    $displayedPages[] = 1;

                    if ($currentPage > 3) {
                        $displayedPages[] = '...';
                    }

                    for ($i = max(2, $currentPage - 1); $i <= min($lastPage - 1, $currentPage + 1); $i++) {
                        if ($i != 1 && $i != $lastPage) {
                            $displayedPages[] = $i;
                        }
                    }

                    if ($currentPage < $lastPage - 2) {
                        $displayedPages[] = '...';
                    }

                    if ($lastPage > 1) {
                        $displayedPages[] = $lastPage;
                    }
                }
            @endphp

            {{-- Render Pagination Links --}}
            @foreach ($displayedPages as $page)
                @if ($page === '...')
                    <li class="page-item disabled"><span class="page-link">{{ $page }}</span></li>
                @else
                    @if ($page == $currentPage)
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $product->appends(request()->query())->url($page) }}">{{ $page }}</a>
                        </li>
                    @endif
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($product->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $product->appends(request()->query())->nextPageUrl() }}">&raquo;</a>
                </li>
            @else
                <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
            @endif
        </ul>
    </nav>
</div>
