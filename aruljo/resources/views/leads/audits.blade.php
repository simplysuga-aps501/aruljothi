@extends('adminlte::page')

@section('title', 'Audit History')

@section('content_header')
    <h2 class="text-lg mb-3">Audit History for Lead #{{ $lead->id }}</h2>
@stop

@section('content')
    @if($audits->isEmpty())
        <p class="text-muted">No audit records found for this lead.</p>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Changes</th>
                    </tr>
                </thead>
                <tbody style="font-size: 0.925rem">
                    @foreach($audits as $audit)
                        <tr>
                            <td>{{ $audit->created_at->toDayDateTimeString() }}</td>
                            <td>{{ optional($audit->user)->name ?? 'System' }}</td>
                            <td>{{ ucfirst($audit->event) }}</td>
                            <td>
                                <ul class="mb-0 ps-3">
                                    @foreach($audit->new_values as $key => $value)
                                        @php
                                            $old = $audit->old_values[$key] ?? '—';
                                        @endphp
                                        <li>
                                            <strong>{{ $key }}</strong>:
                                            <span class="text-danger text-decoration-line-through">{{ $old }}</span>
                                            →
                                            <span class="text-success">{{ $value }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <a href="{{ route('leads.index') }}" class="btn btn-link mt-3">← Back to Leads</a>
@stop
