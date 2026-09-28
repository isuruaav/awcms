@if ($paginator->hasPages())
    @php($pagerSinhala = app()->getLocale() === 'si')
    <nav class="aw-gallery-pager" aria-label="{{ $pagerSinhala ? 'පිටු තේරීම' : 'Pagination' }}">
        @if ($paginator->onFirstPage())
            <span class="aw-gallery-page-button" aria-disabled="true">← {{ $pagerSinhala ? 'පෙර' : 'Previous' }}</span>
        @else
            <a class="aw-gallery-page-button" href="{{ $paginator->previousPageUrl() }}" rel="prev">← {{ $pagerSinhala ? 'පෙර' : 'Previous' }}</a>
        @endif
        <div class="aw-gallery-page-numbers">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="aw-gallery-page-gap" aria-hidden="true">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="aw-gallery-page-button" aria-current="page" aria-label="{{ $pagerSinhala ? 'පිටුව '.$page : 'Page '.$page }}">{{ $page }}</span>
                        @else
                            <a class="aw-gallery-page-button" href="{{ $url }}" aria-label="{{ $pagerSinhala ? 'පිටුව '.$page : 'Page '.$page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>
        <span class="aw-gallery-page-count">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a class="aw-gallery-page-button" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ $pagerSinhala ? 'ඊළඟ' : 'Next' }} →</a>
        @else
            <span class="aw-gallery-page-button" aria-disabled="true">{{ $pagerSinhala ? 'ඊළඟ' : 'Next' }} →</span>
        @endif
    </nav>
@endif
