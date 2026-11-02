{{-- Choix de la langue sans compte : un bouton par langue, fonctionne sans JavaScript. --}}
<form method="POST" action="{{ route('locale.update') }}">
    @csrf
    <fieldset class="m-0 p-0 border-0">
        <legend class="visually-hidden">{{ __('public.language.label') }}</legend>
        <ul class="samm-locales">
            @foreach (config('samm.locales') as $code => $name)
                <li>
                    <button type="submit"
                            name="locale"
                            value="{{ $code }}"
                            class="samm-locale"
                            lang="{{ $code }}"
                            @if (app()->getLocale() === $code) aria-current="true" @endif>
                        @if ($flag = config('samm.locale_flags.'.$code))
                            <x-samm.flag :code="$flag" />
                        @endif
                        <span>{{ $name }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </fieldset>
</form>
