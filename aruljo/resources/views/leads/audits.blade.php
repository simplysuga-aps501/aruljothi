<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">
            Audit History for Lead #{{ $lead->id }}
        </h2>
    </x-slot>

    @if($audits->isEmpty())
        <p class="text-gray-600">No audit records found for this lead.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 shadow-sm rounded-lg">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Date</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">User</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Event</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($audits as $audit)
                        <tr class="border-t">
                            <td class="px-4 py-2 text-sm text-gray-800">{{ $audit->created_at->toDayDateTimeString() }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">
                                {{ optional($audit->user)->name ?? 'System' }}
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-800">{{ ucfirst($audit->event) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800">
                                <ul class="list-disc list-inside">
                                    @foreach($audit->new_values as $key => $value)
                                        @php
                                            $old = $audit->old_values[$key] ?? '—';
                                        @endphp
                                        <li>
                                            <strong>{{ $key }}</strong>:
                                            <span class="text-red-600 line-through">{{ $old }}</span>
                                            →
                                            <span class="text-green-600">{{ $value }}</span>
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

    <div class="mt-6">
        <a href="{{ route('leads.index') }}" class="text-blue-600 hover:underline">← Back to Leads</a>
    </div>
</x-app-layout>
