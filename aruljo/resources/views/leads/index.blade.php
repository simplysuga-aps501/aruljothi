<x-app-layout>
    @push('styles')
        <!--Datatable CSS-->
        <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css">
        <!--Responsive CSS-->
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css">
        <!--Column Filter-->
        <link rel="stylesheet" href="https://cdn.datatables.net/columncontrol/1.0.6/css/columnControl.dataTables.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.5/css/dataTables.dateTime.min.css">

        <style>
            div.dt-layout-row {
                margin-bottom: 1rem;
                padding-left: 1rem;
                padding-right: 1rem;
            }
        </style>
    @endpush
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">All Leads</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif




        <div class=" bg-white shadow-md rounded-lg overflow-x-auto">
            <table id="leads_table"
                class="w-full table-auto text-sm border border-gray-200 whitespace-nowrap opacity-0 transition-opacity duration-500">
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
                            <td class="px-3 py-2 border">
                                {{ \Carbon\Carbon::parse($lead->lead_date)->format('d-m-Y h:i A') }}</td>
                            <td class="px-3 py-2 border">{{ $lead->buyer_name }}</td>
                            <td class="px-3 py-2 border">
                                <a href="tel:{{ $lead->buyer_contact }}"
                                    onclick="copyPhone(event, '{{ $lead->buyer_contact }}')"
                                    class="text-blue-600 hover:underline cursor-pointer">
                                    {{ $lead->buyer_contact }}
                                </a>
                            </td>
                            <td class="px-3 py-2 border">{{ $lead->status }}</td>
                            <td class="px-3 py-2 border">{{ $lead->assigned_to }}</td>


                            <td class="px-3 py-2 border">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <a href="{{ route('leads.edit', $lead->id) }}"
                                        class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">
                                        Update
                                    </a>
                                    <a href="{{ route('leads.audits', $lead->id) }}"
                                        class="bg-blue-600 text-white px-3 py-1 rounded text-xs hover:bg-blue-700">Logs</a>

                                    <form action="{{ route('leads.destroy', $lead->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this lead?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
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
        @push('scripts')
            <!--JQuery JS-->
            <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

            <!--Datatable JS-->
            <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>

            <!--Responsive JS-->
            <script src="https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js"></script>
            <script src="https://cdn.datatables.net/responsive/3.0.4/js/responsive.dataTables.js"></script>

            <!-- Column Filtering -->
            <script src="https://cdn.datatables.net/columncontrol/1.0.6/js/dataTables.columnControl.js"></script>
            <script src="https://cdn.datatables.net/columncontrol/1.0.6/js/columnControl.dataTables.js"></script>


            <script>
                new DataTable('#leads_table', {
                    responsive: true,
                    stateSave: true,
                    columnControl: ['order', ['search']],
                    columnDefs: [{
                        targets: [5, 6],
                        columnControl: ['order', ['searchList']]
                    }],
                    ordering: {
                        indicators: false,
                        handler: false
                    },
                    initComplete: function() {
                        document.querySelector('#leads_table').classList.remove('opacity-0');
                    }
                });

                function copyPhone(event, number) {
                    // Only copy on desktop
                    if (!/Mobi|Android|iPhone/i.test(navigator.userAgent)) {
                        event.preventDefault(); // Prevent dial
                        navigator.clipboard.writeText(number)
                            .then(() => alert('Phone number copied: ' + number))
                            .catch(() => alert('Failed to copy number.'));
                    }
                }
            </script>
        @endpush
</x-app-layout>
