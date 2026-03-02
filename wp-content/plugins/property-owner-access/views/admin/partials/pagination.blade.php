@php
    // If paginatedData is explicitly passed, use it; otherwise auto-detect
    $paginatedData ??= !empty($dataType) 
        ? match($dataType) {
            'reservations' => $reservations ?? null,
            'expenses' => $expenses ?? null,
            'transfers' => $transfers ?? null,
            default => $reservations ?? null,
          }
        : ($reservations ?? $expenses ?? $transfers);
    
    // Validate that we have a valid paginator object
    if (!is_object($paginatedData) || !method_exists($paginatedData, 'currentPage')) {
        $paginatedData = null;
    }
@endphp

@if ($paginatedData)
    @if ($paginatedData->lastPage() > 1)
        @php
            $query = $_GET;
            unset($query['page']);
            $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
            $makePageUrl = function($page) use ($baseUrl, $query) {
                return $baseUrl . '?' . http_build_query(array_merge($query, ['page' => $page]));
            };
            $total = $paginatedData->lastPage();
            $current = $paginatedData->currentPage();
            $range = 2;
            $showAll = $total <= 7; // Show all pages if 7 or fewer
        @endphp
        <div class="navigation">
            {{-- Previous Page Link --}}
            @if ($current > 1)
                <a href="{{ $makePageUrl($current - 1) }}">&laquo;</a>
            @endif

            {{-- Always show all pages if total <= 7 --}}
            @if ($showAll)
                @for ($i = 1; $i <= $total; $i++)
                    <a href="{{ $makePageUrl($i) }}" class="{{ $current == $i ? 'active' : '' }}">{{ $i }}</a>
                @endfor
            @else
                {{-- Always show first page --}}
                <a href="{{ $makePageUrl(1) }}" class="{{ $current == 1 ? 'active' : '' }}">1</a>

                {{-- Show left ellipsis if there is a gap between first page and the start of the range --}}
                {{-- If the current page minus the range is greater than 2, it means there are hidden pages between page 1 and the start of the range --}}
                @if ($current - $range > 2)
                    <span>...</span>
                @endif

                {{-- Show pages around the current page, excluding first and last --}}
                @for ($i = max(2, $current - $range); $i <= min($total - 1, $current + $range); $i++)
                    <a href="{{ $makePageUrl($i) }}" class="{{ $current == $i ? 'active' : '' }}">{{ $i }}</a>
                @endfor

                {{-- Show right ellipsis if there is a gap between end of the range and last page --}}
                {{-- If the current page plus the range is less than total - 1, it means there are hidden pages between end of the range and last page --}}
                @if ($current + $range < $total - 1)
                    <span>...</span>
                @endif

                {{-- Always show last page --}}
                @if ($total > 1)
                    <a href="{{ $makePageUrl($total) }}" class="{{ $current == $total ? 'active' : '' }}">{{ $total }}</a>
                @endif
            @endif

            {{-- Next Page Link --}}
            @if ($current < $total)
                <a href="{{ $makePageUrl($current + 1) }}">&raquo;</a>
            @endif
        </div>
    @endif
@endif