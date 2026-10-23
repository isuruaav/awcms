@if ($paginator->hasPages())
    @php($pagerSinhala = app()->getLocale() === 'si')
    <nav class="school-news-pager" aria-label="{{ __('public.news_page.pagination') }}">
        @if ($paginator->onFirstPage())
            <span class="school-news-page-button" aria-disabled="true">← {{ $pagerSinhala ? 'පෙර' : 'Previous' }}</span>
        @else
            <a class="school-news-page-button" href="{{ $paginator->previousPageUrl() }}" rel="prev">← {{ $pagerSinhala ? 'පෙර' : 'Previous' }}</a>
        @endif

        <div class="school-news-page-numbers">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="school-news-page-gap" aria-hidden="true">{{ $element }}</span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span class="school-news-page-button" aria-current="page" aria-label="{{ $pagerSinhala ? 'පිටුව '.$page : 'Page '.$page }}">{{ $page }}</span>
                        @else
                            <a class="school-news-page-button" href="{{ $url }}" aria-label="{{ $pagerSinhala ? 'පිටුව '.$page : 'Page '.$page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        <span class="school-news-page-count">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="school-news-page-button" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ $pagerSinhala ? 'ඊළඟ' : 'Next' }} →</a>
        @else
            <span class="school-news-page-button" aria-disabled="true">{{ $pagerSinhala ? 'ඊළඟ' : 'Next' }} →</span>
        @endif
    </nav>
@endif
