<section class="py-24 sm:py-32 px-6 bg-white overflow-hidden">
    <div class="max-w-3xl mx-auto">

        {{-- Root — central block with triangular wings --}}
        <div class="flex justify-center items-center mb-10">
            {{-- Left triangle --}}
            <div class="w-0 h-0 border-y-[10px] border-y-transparent border-r-[14px] border-r-gray-800"></div>
            {{-- Center label --}}
            <span class="inline-flex items-center gap-2.5 px-6 py-2.5 bg-gray-800 text-white text-sm font-bold tracking-wide uppercase shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                {{ __('landing.pipeline_tree.root') }}
            </span>
            {{-- Right triangle --}}
            <div class="w-0 h-0 border-y-[10px] border-y-transparent border-l-[14px] border-l-gray-800"></div>
        </div>

        {{-- Labels — bold section headers with accent lines --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-8 h-0.5 bg-red-400"></div>
                <span class="text-sm font-extrabold uppercase tracking-[0.15em] text-red-500">{{ __('landing.pipeline_tree.without') }}</span>
                <div class="w-8 h-0.5 bg-red-400"></div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-8 h-0.5 bg-amber-400"></div>
                <span class="text-sm font-extrabold uppercase tracking-[0.15em] text-amber-500">{{ __('landing.pipeline_tree.with') }}</span>
                <div class="w-8 h-0.5 bg-amber-400"></div>
            </div>
        </div>

        {{-- Tree: centered trunk --}}
        <div class="relative flex justify-center">
            <div class="absolute left-1/2 -translate-x-1/2 top-0 bottom-0 w-0.5 bg-gray-300"></div>

            <div class="relative w-full space-y-6">

                @php
                $treeNodes = __('landing.pipeline_tree.branches');
                $branches = [
                    ['side' => 'right', 'color' => 'red',   'nodes' => $treeNodes[0], 'offset' => 4],
                    ['side' => 'left',  'color' => 'amber', 'nodes' => $treeNodes[1], 'offset' => 4],
                    ['side' => 'right', 'color' => 'red',   'nodes' => $treeNodes[2], 'offset' => 16],
                    ['side' => 'left',  'color' => 'amber', 'nodes' => $treeNodes[3], 'offset' => 16],
                    ['side' => 'right', 'color' => 'red',   'nodes' => $treeNodes[4], 'offset' => 28],
                    ['side' => 'left',  'color' => 'amber', 'nodes' => $treeNodes[5], 'offset' => 28],
                    ['side' => 'right', 'color' => 'red',   'nodes' => $treeNodes[6], 'offset' => 40],
                ];
                @endphp

                @foreach ($branches as $branch)
                    @php
                        $isRed = $branch['color'] === 'red';
                        $isLeft = $branch['side'] === 'left';
                        $lineColor = $isRed ? 'bg-red-200' : 'bg-amber-300';
                        $pillBg = $isRed ? 'bg-gray-100 text-gray-500' : 'bg-amber-50/50 text-amber-700';
                        $lastNode = $branch['nodes'][count($branch['nodes']) - 1];
                        $endBg = (str_starts_with($lastNode, '❌') || str_starts_with($lastNode, '⏱️')) ? 'bg-red-50 text-red-600 border-red-100'
                               : 'bg-amber-50 text-amber-700 border-amber-200';
                        $arrowColor = $isRed ? 'text-gray-300' : 'text-amber-300';
                    @endphp

                    <div class="flex items-start {{ $isLeft ? 'flex-row' : 'flex-row-reverse' }}">
                        {{-- Left/right spacer from center --}}
                        <div class="w-1/2"></div>

                        {{-- Horizontal connector line --}}
                        <div class="h-0.5 {{ $lineColor }} mt-5" style="width: {{ $branch['offset'] * 4 }}px"></div>

                        {{-- Branch pills — vertical stack --}}
                        <div class="flex flex-col items-{{ $isLeft ? 'start' : 'end' }} gap-1 ml-3">
                            @foreach ($branch['nodes'] as $j => $node)
                                @if ($j > 0)
                                    <svg class="w-3.5 h-3.5 {{ $arrowColor }} -my-0.5 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                @endif
                                @php $isLast = $j === count($branch['nodes']) - 1; @endphp
                                <span class="px-3 py-1.5 text-xs font-medium rounded-full whitespace-nowrap {{ $isLast ? $endBg . ' font-bold border' : $pillBg }}">
                                    {{ $node }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

    </div>
</section>
