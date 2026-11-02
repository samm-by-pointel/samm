{{-- Panneau d'actions du gestionnaire : chaque bloc n'apparaît qu'avec la capacité, mais c'est la policy qui refuse. --}}
@if ($can['assign'] || $can['updateStatus'] || $can['changePriority'] || $can['reply'])
    <section class="samm-card samm-section samm-actions" aria-labelledby="actions-title">
        <h2 id="actions-title" class="samm-heading">{{ __('manage.cases.actions.title') }}</h2>

        @if (session('status'))
            <div class="samm-alert samm-alert-succes mt-0" role="status">
                <i class="bi bi-check-circle" aria-hidden="true"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        <div class="samm-actions-grid">
            @if ($can['assign'])
                <form method="POST" action="{{ route('manage.cases.assignment.store', $case) }}" novalidate>
                    @csrf
                    <label for="assignee" class="form-label">{{ __('manage.cases.actions.assignment.label') }}</label>
                    <select id="assignee" name="assignee" class="form-select @error('assignee') is-invalid @enderror">
                        <option value="">{{ __('manage.cases.actions.assignment.none') }}</option>
                        @foreach ($assignableUsers as $user)
                            <option value="{{ $user->uuid }}" @selected(old('assignee', $case->assigned_to_user_id === $user->id ? $user->uuid : null) === $user->uuid)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('assignee')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-outline-secondary mt-2">{{ __('manage.cases.actions.assignment.submit') }}</button>
                </form>
            @endif

            @if ($can['updateStatus'])
                <form method="POST" action="{{ route('manage.cases.status.store', $case) }}" novalidate>
                    @csrf
                    <label for="status" class="form-label">{{ __('manage.cases.actions.status.label') }}</label>
                    @if ($allowedTransitions === [])
                        <p class="samm-muted">{{ __('manage.cases.actions.status.none') }}</p>
                    @else
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="">{{ __('manage.cases.actions.status.choose') }}</option>
                            @foreach ($allowedTransitions as $transition)
                                <option value="{{ $transition->value }}" @selected(old('status') === $transition->value)>{{ $transition->label() }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <label for="comment" class="form-label mt-2">{{ __('manage.cases.actions.status.comment') }}</label>
                        <p class="samm-caption">{{ __('manage.cases.actions.status.comment_help') }}</p>
                        <textarea id="comment" name="comment" rows="2" class="form-control @error('comment') is-invalid @enderror" maxlength="2000">{{ old('comment') }}</textarea>
                        @error('comment')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn btn-outline-secondary mt-2">{{ __('manage.cases.actions.status.submit') }}</button>
                    @endif
                </form>
            @endif

            @if ($can['changePriority'])
                <form method="POST" action="{{ route('manage.cases.priority.store', $case) }}" novalidate>
                    @csrf
                    <label for="priority" class="form-label">{{ __('manage.cases.actions.priority.label') }}</label>
                    <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror">
                        @foreach (\App\Modules\Cases\Enums\CasePriority::cases() as $priority)
                            <option value="{{ $priority->value }}" @selected(old('priority', $case->priority->value) === $priority->value)>{{ $priority->label() }}</option>
                        @endforeach
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-outline-secondary mt-2">{{ __('manage.cases.actions.priority.submit') }}</button>
                </form>
            @endif
        </div>
    </section>
@endif
