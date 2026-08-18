@props([
    'blocks' => [],
])

@if (count($blocks) > 0)
    <div
        class="awcms-page-blocks
               mt-10 space-y-8"
    >
        @foreach ($blocks as $block)
            @php
                $type = is_string(
                    $block['type'] ?? null
                )
                    ? $block['type']
                    : '';

                $data = is_array(
                    $block['data'] ?? null
                )
                    ? $block['data']
                    : [];

                $blockId = is_string(
                    $block['id'] ?? null
                )
                    ? $block['id']
                    : 'block-' . $loop->index;
            @endphp

            <section
                id="page-block-{{ $blockId }}"
                class="awcms-page-block"
            >
                {{-- Heading --}}
                @if ($type === 'heading')
                    @php
                        $level = is_string(
                            $data['level'] ?? null
                        )
                            ? $data['level']
                            : 'h2';

                        $text = is_string(
                            $data['text'] ?? null
                        )
                            ? $data['text']
                            : '';
                    @endphp

                    @if ($text !== '')
                        @if ($level === 'h4')
                            <h4
                                class="text-xl font-bold
                                       tracking-tight
                                       text-zinc-900"
                            >
                                {{ $text }}
                            </h4>
                        @elseif ($level === 'h3')
                            <h3
                                class="text-2xl font-bold
                                       tracking-tight
                                       text-zinc-900"
                            >
                                {{ $text }}
                            </h3>
                        @else
                            <h2
                                class="text-3xl font-bold
                                       tracking-tight
                                       text-zinc-950"
                            >
                                {{ $text }}
                            </h2>
                        @endif
                    @endif

                {{-- Rich Text --}}
                @elseif ($type === 'text')
                    @php
                        $content = is_string(
                            $data['content'] ?? null
                        )
                            ? $data['content']
                            : '';
                    @endphp

                    @if ($content !== '')
                        <div
                            class="trix-content
                                   awcms-content"
                        >
                            {!! $content !!}
                        </div>
                    @endif

                {{-- Image --}}
                @elseif ($type === 'image')
                    @php
                        $src = is_string(
                            $data['src'] ?? null
                        )
                            ? $data['src']
                            : '';

                        $alt = is_string(
                            $data['alt'] ?? null
                        )
                            ? $data['alt']
                            : '';

                        $caption = is_string(
                            $data['caption'] ?? null
                        )
                            ? $data['caption']
                            : '';

                        $alignment = is_string(
                            $data['alignment'] ?? null
                        )
                            ? $data['alignment']
                            : 'center';

                        $figureClass = match ($alignment) {
                            'left' =>
                                'mr-auto max-w-3xl',

                            'right' =>
                                'ml-auto max-w-3xl',

                            'full' =>
                                'w-full',

                            default =>
                                'mx-auto max-w-4xl',
                        };
                    @endphp

                    @if ($src !== '')
                        <figure
                            class="{{ $figureClass }}"
                        >
                            <img
                                src="{{ $src }}"
                                alt="{{ $alt }}"
                                loading="lazy"
                                decoding="async"
                                class="h-auto w-full
                                       rounded-2xl
                                       object-cover"
                            >

                            @if ($caption !== '')
                                <figcaption
                                    class="mt-3
                                           text-center
                                           text-sm
                                           leading-6
                                           text-zinc-500"
                                >
                                    {{ $caption }}
                                </figcaption>
                            @endif
                        </figure>
                    @endif

                {{-- Button --}}
                @elseif ($type === 'button')
                    @php
                        $label = is_string(
                            $data['label'] ?? null
                        )
                            ? $data['label']
                            : '';

                        $url = is_string(
                            $data['url'] ?? null
                        )
                            ? $data['url']
                            : '';

                        $target = (
                            $data['target'] ?? null
                        ) === '_blank'
                            ? '_blank'
                            : '_self';

                        $style = is_string(
                            $data['style'] ?? null
                        )
                            ? $data['style']
                            : 'primary';

                        $buttonClass = match ($style) {
                            'secondary' =>
                                'bg-zinc-800 text-white hover:bg-zinc-900',

                            'outline' =>
                                'border border-emerald-700 bg-white text-emerald-800 hover:bg-emerald-50',

                            default =>
                                'bg-emerald-700 text-white hover:bg-emerald-800',
                        };
                    @endphp

                    @if (
                        $label !== ''
                        && $url !== ''
                    )
                        <div>
                            <a
                                href="{{ $url }}"
                                target="{{ $target }}"
                                @if ($target === '_blank')
                                    rel="noopener noreferrer"
                                @endif
                                class="inline-flex
                                       items-center
                                       justify-center
                                       rounded-xl
                                       px-5 py-3
                                       text-sm font-bold
                                       transition
                                       {{ $buttonClass }}"
                            >
                                {{ $label }}
                            </a>
                        </div>
                    @endif

                {{-- Two Columns --}}
                @elseif ($type === 'two_columns')
                    @php
                        $left = is_string(
                            $data['left'] ?? null
                        )
                            ? $data['left']
                            : '';

                        $right = is_string(
                            $data['right'] ?? null
                        )
                            ? $data['right']
                            : '';

                        $ratio = is_string(
                            $data['ratio'] ?? null
                        )
                            ? $data['ratio']
                            : '50-50';

                        $gridClass = match ($ratio) {
                            '40-60' =>
                                'lg:grid-cols-[2fr_3fr]',

                            '60-40' =>
                                'lg:grid-cols-[3fr_2fr]',

                            default =>
                                'lg:grid-cols-2',
                        };
                    @endphp

                    <div
                        class="grid gap-8
                               {{ $gridClass }}"
                    >
                        <div
                            class="min-w-0
                                   trix-content
                                   awcms-content"
                        >
                            {!! $left !!}
                        </div>

                        <div
                            class="min-w-0
                                   trix-content
                                   awcms-content"
                        >
                            {!! $right !!}
                        </div>
                    </div>

                {{-- Callout --}}
                @elseif ($type === 'callout')
                    @php
                        $title = is_string(
                            $data['title'] ?? null
                        )
                            ? $data['title']
                            : '';

                        $content = is_string(
                            $data['content'] ?? null
                        )
                            ? $data['content']
                            : '';

                        $style = is_string(
                            $data['style'] ?? null
                        )
                            ? $data['style']
                            : 'info';

                        $calloutClass = match ($style) {
                            'success' =>
                                'border-emerald-200 bg-emerald-50',

                            'warning' =>
                                'border-amber-200 bg-amber-50',

                            'neutral' =>
                                'border-zinc-200 bg-zinc-50',

                            default =>
                                'border-blue-200 bg-blue-50',
                        };
                    @endphp

                    @if (
                        $title !== ''
                        || $content !== ''
                    )
                        <aside
                            class="rounded-2xl
                                   border p-6
                                   {{ $calloutClass }}"
                        >
                            @if ($title !== '')
                                <h3
                                    class="text-lg
                                           font-bold
                                           text-zinc-900"
                                >
                                    {{ $title }}
                                </h3>
                            @endif

                            @if ($content !== '')
                                <div
                                    class="trix-content
                                           awcms-content
                                           {{ $title !== ''
                                                ? 'mt-3'
                                                : '' }}"
                                >
                                    {!! $content !!}
                                </div>
                            @endif
                        </aside>
                    @endif

                {{-- Divider --}}
                @elseif ($type === 'divider')
                    <hr
                        class="border-zinc-200"
                    >

                {{-- Spacer --}}
                @elseif ($type === 'spacer')
                    @php
                        $size = is_string(
                            $data['size'] ?? null
                        )
                            ? $data['size']
                            : 'medium';

                        $spacerClass = match ($size) {
                            'small' =>
                                'h-4',

                            'large' =>
                                'h-16',

                            default =>
                                'h-8',
                        };
                    @endphp

                    <div
                        aria-hidden="true"
                        class="{{ $spacerClass }}"
                    ></div>
                @endif
            </section>
        @endforeach
    </div>
@endif