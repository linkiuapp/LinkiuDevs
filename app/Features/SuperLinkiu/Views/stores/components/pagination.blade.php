{{-- ================================================================ --}}
{{-- PAGINACIÓN --}}
{{-- ================================================================ --}}

@if ($stores->hasPages())
<div class="mt-6">
    <ul class="pagination flex flex-wrap items-center gap-2 justify-center">
        {{-- Previous Page Link --}}
        @if ($stores->onFirstPage())
            <li class="page-item">
                <span class="page-link bg-white-100 border border-white-200 text-black-200 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] cursor-not-allowed">
                    <x-solar-arrow-left-outline class="w-4 h-4" />
                </span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link bg-white-50 border border-white-200 text-black-400 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" 
                   href="{{ $stores->previousPageUrl() }}">
                    <x-solar-arrow-left-outline class="w-4 h-4" />
                </a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($stores->getUrlRange(1, $stores->lastPage()) as $page => $url)
            @if ($page == $stores->currentPage())
                <li class="page-item">
                    <span class="page-link bg-primary-200 text-white-50 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] w-[40px]">
                        {{ $page }}
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link bg-white-50 border border-white-200 text-black-400 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] w-[40px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" 
                       href="{{ $url }}">
                        {{ $page }}
                    </a>
                </li>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($stores->hasMorePages())
            <li class="page-item">
                <a class="page-link bg-white-50 border border-white-200 text-black-400 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] hover:bg-primary-50 hover:border-primary-200 hover:text-primary-400 transition-colors" 
                   href="{{ $stores->nextPageUrl() }}">
                    <x-solar-arrow-right-outline class="w-4 h-4" />
                </a>
            </li>
        @else
            <li class="page-item">
                <span class="page-link bg-white-100 border border-white-200 text-black-200 font-medium rounded-lg px-3 py-2 flex items-center justify-center h-[40px] cursor-not-allowed">
                    <x-solar-arrow-right-outline class="w-4 h-4" />
                </span>
            </li>
        @endif
    </ul>
</div>
@endif 