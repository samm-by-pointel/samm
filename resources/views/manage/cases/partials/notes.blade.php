{{-- Notes internes : visibles de l'équipe qui voit le dossier, jamais de la personne qui signale. --}}
<section class="samm-section" aria-labelledby="notes-title">
    <h2 id="notes-title" class="samm-heading">{{ __('manage.cases.notes.title') }}</h2>
    <p class="samm-caption">{{ __('manage.cases.notes.help') }}</p>

    @if ($notes->isEmpty())
        <p class="samm-muted">{{ __('manage.cases.notes.empty') }}</p>
    @else
        <ol class="samm-timeline mb-3">
            @foreach ($notes as $note)
                <li>
                    <div>
                        <p class="samm-prose mb-1">{{ $note->body }}</p>
                        <span class="samm-caption">{{ $note->created_at->translatedFormat('d M Y, H:i') }} · {{ $note->author?->name ?? __('audit.actor_kinds.system') }}</span>
                    </div>
                </li>
            @endforeach
        </ol>
    @endif

    @if ($can['addNote'])
        <form method="POST" action="{{ route('manage.cases.notes.store', $case) }}" novalidate class="samm-card">
            @csrf
            <label for="note" class="form-label">{{ __('manage.cases.notes.compose') }}</label>
            <textarea id="note" name="note" rows="3" class="form-control @error('note') is-invalid @enderror" maxlength="5000" required>{{ old('note') }}</textarea>
            @error('note')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <button type="submit" class="btn btn-outline-secondary mt-2">{{ __('manage.cases.notes.submit') }}</button>
        </form>
    @endif
</section>
