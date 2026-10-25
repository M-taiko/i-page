{{-- Shared audit table. Expects $logs (paginator); optional $showOrganization (bool). --}}
@php
    $showOrganization = $showOrganization ?? false;
    $eventVariant = fn ($e) => match ($e) { 'created' => 'success', 'updated' => 'info', 'deleted' => 'danger', 'restored' => 'warning', default => 'secondary' };
@endphp
<div class="table-responsive">
    <table class="table table-hover text-md-nowrap mb-0">
        <thead>
            <tr>
                <th>{{ __('When') }}</th><th>{{ __('Event') }}</th><th>{{ __('Subject') }}</th>
                <th>{{ __('Details') }}</th><th>{{ __('By') }}</th>
                @if ($showOrganization)<th>{{ __('Organization') }}</th>@endif
                <th>{{ __('IP') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td class="text-muted" title="{{ $log->created_at }}">{{ $log->created_at->format('M d, H:i') }}<div class="small">{{ $log->created_at->diffForHumans() }}</div></td>
                    <td><x-valex.badge :variant="$eventVariant($log->event)">{{ $log->event }}</x-valex.badge></td>
                    <td>{{ \App\Services\CrmService::subjectLabel($log) }}</td>
                    <td style="white-space: normal; max-width: 340px;">
                        {{ $log->description ?: '—' }}
                        @if (! empty($log->changes))
                            <details class="small text-muted"><summary>{{ __('Changes') }}</summary><pre class="mb-0 small">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>
                        @endif
                    </td>
                    <td>{{ $log->user?->full_name ?? __('System') }}</td>
                    @if ($showOrganization)
                        <td>@if ($log->organization)<a href="{{ route('admin.crm.organization', $log->organization) }}">{{ $log->organization->name }}</a>@else — @endif</td>
                    @endif
                    <td class="text-muted small">{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">{{ __('No activity found') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
