@props([
    'items' => []
])

<nav class="flex items-center text-sm text-black-300 mb-4" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
        @foreach($items as $index => $item)
            <li class="flex items-center">
                @if($index > 0)
                    <span class="mx-2 text-black-200">/</span>
                @endif
                
                @if(isset($item['url']) && $item['url'] && $index < count($items) - 1)
                    <a href="{{ $item['url'] }}" 
                       class="hover:text-primary-300 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-200 rounded px-1"
                       @if(isset($item['title'])) title="{{ $item['title'] }}" @endif>
                        @if(isset($item['icon']))
                            <x-dynamic-component :component="$item['icon']" class="w-4 h-4 inline mr-1" />
                        @endif
                        {{ $item['name'] }}
                    </a>
                @else
                    <span class="text-black-400 font-medium flex items-center">
                        @if(isset($item['icon']))
                            <x-dynamic-component :component="$item['icon']" class="w-4 h-4 inline mr-1" />
                        @endif
                        {{ $item['name'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>