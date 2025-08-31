@props([
    'title' => '',
    'description' => '',
    'actions' => []
])

<div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
    <div class="flex-1">
        @if($title)
            <h1 class="text-lg font-bold text-black-400">{{ $title }}</h1>
        @endif
        @if($description)
            <p class="text-sm text-black-300 mt-1">{{ $description }}</p>
        @endif
    </div>
    
    @if(count($actions) > 0)
        <div class="flex flex-col sm:flex-row gap-3">
            @foreach($actions as $action)
                @if(isset($action['type']) && $action['type'] === 'button')
                    <button 
                        @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                        class="{{ $action['class'] ?? 'bg-primary-200 hover:bg-primary-300 text-accent-50 px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors' }}"
                        @if(isset($action['title'])) title="{{ $action['title'] }}" @endif
                    >
                        @if(isset($action['icon']))
                            <x-dynamic-component :component="$action['icon']" class="w-5 h-5" />
                        @endif
                        <span class="{{ isset($action['responsive_text']) ? 'hidden sm:inline' : '' }}">{{ $action['text'] }}</span>
                        @if(isset($action['responsive_text']))
                            <span class="sm:hidden">{{ $action['responsive_text'] }}</span>
                        @endif
                    </button>
                @else
                    <a 
                        href="{{ $action['url'] }}" 
                        class="{{ $action['class'] ?? 'bg-accent-200 hover:bg-accent-300 text-black-400 px-4 py-2 rounded-lg flex items-center justify-center gap-2 transition-colors' }}"
                        @if(isset($action['title'])) title="{{ $action['title'] }}" @endif
                    >
                        @if(isset($action['icon']))
                            <x-dynamic-component :component="$action['icon']" class="w-5 h-5" />
                        @endif
                        <span class="{{ isset($action['responsive_text']) ? 'hidden sm:inline' : '' }}">{{ $action['text'] }}</span>
                        @if(isset($action['responsive_text']))
                            <span class="sm:hidden">{{ $action['responsive_text'] }}</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    @endif
</div>