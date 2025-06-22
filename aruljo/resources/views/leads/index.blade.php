<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">All Leads</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('leads.create') }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                + Create Lead
            </a>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block bg-white shadow-md rounded-lg overflow-x-auto">
            <table class="min-w-full table-auto text-sm border border-gray-200">
                <thead class="bg-gray-100 text-left text-xs sm:text-sm">
                    <tr>
                        <th class="px-3 py-2 border">#</th>
                        <th class="px-3 py-2 border">Platform</th>
                        <th class="px-3 py-2 border">Lead Date</th>
                        <th class="px-3 py-2 border">Buyer</th>
                        <th class="px-3 py-2 border">Contact</th>
                        <th class="px-3 py-2 border">Status</th>
                        <th class="px-3 py-2 border">Assigned To</th>
                        <th class="px-3 py-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $index => $lead)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-3 py-2 border">{{ $lead->platform }}</td>
                            <td class="px-3 py-2 border">{{ \Carbon\Carbon::parse($lead->lead_date)->format('d-m-Y h:i A') }}</td>
                            <td class="px-3 py-2 border">{{ $lead->buyer_name }}</td>
                            <td class="px-3 py-2 border">{{ $lead->buyer_contact }}</td>
                            <td class="px-3 py-2 border">
                                <form action="{{ route('leads.update', $lead->id) }}" method="POST" class="flex flex-col gap-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="status"
                                        class="w-full sm:min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                        @foreach(['New Lead', 'Lead Followup', 'In Progress', 'Quotation', 'PO', 'Cancelled', 'Completed'] as $status)
                                            <option value="{{ $status }}" @selected($lead->status === $status)>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>

                            </td>
                            <td class="px-3 py-2 border">
                               <select name="assigned_to"
                                   class="w-full sm:min-w-[200px] border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                   @foreach($users as $userId => $userName)
                                       <option value="{{ $userName }}"
                                           @selected($lead->assigned_to === $userName || (empty($lead->assigned_to) && $userName === $currentUser))>
                                           {{ $userName }}
                                       </option>
                                   @endforeach
                               </select>

                            </td>
                            <td class="px-3 py-2 border">
                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">Save</button>
                                </form>

                                <a href="{{ route('leads.edit', $lead->id) }}"
                                   class="bg-gray-600 text-white px-3 py-1 rounded text-xs hover:bg-gray-700">Update</a>

                                <a href="{{ route('leads.audits', $lead->id) }}"
                                   class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">Logs</a>

                                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                                        Delete
                                    </button>
                                </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-gray-500">No leads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile View --}}
        <div class="md:hidden space-y-4">
            @forelse($leads as $lead)
                <div class="bg-white border shadow rounded-lg p-4">
                    <div class="flex justify-between text-sm font-semibold text-gray-700 mb-2">
                        <div>{{ $lead->buyer_name }}</div>
                        <div class="text-gray-500">{{ \Carbon\Carbon::parse($lead->lead_date)->format('d-m-Y') }}</div>
                    </div>
                    <div class="text-xs text-gray-600 mb-2">
                        <p><strong>Platform:</strong> {{ $lead->platform }}</p>
                        <p><strong>Contact:</strong> {{ $lead->buyer_contact }}</p>
                        <p><strong>Status:</strong> {{ $lead->status }}</p>
                        <p><strong>Assigned:</strong> {{ $lead->assigned_to }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <a href="{{ route('leads.edit', $lead->id) }}"
                           class="bg-gray-600 text-white px-3 py-1 rounded text-xs hover:bg-gray-700">Update</a>

                        <a href="{{ route('leads.audits', $lead->id) }}"
                           class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">Logs</a>

                        <form action="{{ route('leads.destroy', $lead->id) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this lead?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 text-center">No leads found.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
