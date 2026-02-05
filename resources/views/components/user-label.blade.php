@props(['user'])

@if($user)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }}>
        {{ $user->name }}
        @if($user->trashed())
            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 text-xs font-bold cursor-help"
                title="{{ __('Deleted User') }}"
                aria-label="{{ __('Deleted User') }}">✕</span>
        @endif
    </span>
@else
    <span class="text-gray-400">-</span>
@endif
