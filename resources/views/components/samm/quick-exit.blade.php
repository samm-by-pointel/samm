{{-- Bouton de sortie rapide (charte §12.2) : identique partout, jamais en urgent. Fonctionne sans JavaScript via href. --}}
@php $quickExitUrl ??= config('samm.quick_exit_url'); @endphp
<a class="samm-exit"
   href="{{ $quickExitUrl }}"
   rel="noreferrer"
   data-quick-exit
   data-quick-exit-url="{{ $quickExitUrl }}">
    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
    {{ __('public.quick_exit.label') }}
    <kbd aria-label="{{ __('public.quick_exit.shortcut') }}">{{ __('public.quick_exit.key') }}</kbd>
</a>
