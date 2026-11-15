{{-- Brand admins and moderators, assigned by the organization owner/admins (ADR-015). --}}
<div class="row row-sm">
    <div class="{{ $canAssign ? 'col-lg-7' : 'col-12' }}">
        <x-valex.card :title="__('Brand managers')" :subtitle="__('Admins publish brand posts and manage every brand channel; moderators edit and delete posts across the brand.')">
            <ul class="list-group list-group-flush">
                @forelse ($managers as $manager)
                    <li class="list-group-item d-flex align-items-center flex-wrap px-0" style="gap: 8px;">
                        <span class="avatar avatar-md brround bg-primary-transparent text-primary"><i class="fe fe-{{ $manager->pivot->role === 'admin' ? 'shield' : 'user-check' }}"></i></span>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="font-weight-bold">{{ $manager->full_name }}</div>
                            <small class="text-muted">{{ $manager->email }}</small>
                        </div>
                        <x-valex.badge :variant="$manager->pivot->role === 'admin' ? 'primary' : 'info'">{{ $manager->pivot->role === 'admin' ? __('Brand admin') : __('Brand moderator') }}</x-valex.badge>
                        @if ($canAssign)
                            <form action="{{ route('organizations.brands.managers.store', [$organization, $brand]) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $manager->id }}">
                                <input type="hidden" name="role" value="{{ $manager->pivot->role === 'admin' ? 'moderator' : 'admin' }}">
                                <button class="btn btn-sm btn-light" type="submit">{{ $manager->pivot->role === 'admin' ? __('Make moderator') : __('Make admin') }}</button>
                            </form>
                            <form action="{{ route('organizations.brands.managers.destroy', [$organization, $brand, $manager->id]) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('{{ __('Remove this manager from the brand?') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit" aria-label="{{ __('Remove') }}"><i class="fe fe-x"></i></button>
                            </form>
                        @endif
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">{{ __('No brand managers yet. Organization admins manage every brand.') }}</li>
                @endforelse
            </ul>
        </x-valex.card>
    </div>

    @if ($canAssign)
        <div class="col-lg-5">
            <x-valex.card :title="__('Assign a manager')">
                <form action="{{ route('organizations.brands.managers.store', [$organization, $brand]) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="user_id">{{ __('Team member') }}</label>
                        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                            <option value="">{{ __('Choose…') }}</option>
                            @foreach ($candidates as $candidate)
                                <option value="{{ $candidate->id }}" @selected(old('user_id') == $candidate->id)>{{ $candidate->full_name }} — {{ $candidate->email }}</option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted">{{ __('Only active members of the organization can be assigned.') }}</small>
                    </div>
                    <div class="form-group">
                        <label class="d-block">{{ __('Role') }}</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="role_admin" name="role" value="admin" class="custom-control-input" checked>
                            <label class="custom-control-label" for="role_admin"><strong>{{ __('Brand admin') }}</strong> — <span class="text-muted small">{{ __('posts to the brand, manages all its channels, sees everyone') }}</span></label>
                        </div>
                        <div class="custom-control custom-radio mt-1">
                            <input type="radio" id="role_moderator" name="role" value="moderator" class="custom-control-input">
                            <label class="custom-control-label" for="role_moderator"><strong>{{ __('Brand moderator') }}</strong> — <span class="text-muted small">{{ __('edits and deletes posts, sees everyone') }}</span></label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fe fe-user-plus"></i> {{ __('Assign') }}</button>
                </form>
            </x-valex.card>
        </div>
    @endif
</div>
