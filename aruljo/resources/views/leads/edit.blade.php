<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Edit Lead
        </h2>
    </x-slot>
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-xl p-6">
    <form action="{{ route('leads.update.full', $lead->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
                <select name="platform" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Justdial" @selected($lead->platform === 'Justdial')>Justdial</option>
                    <option value="IndiaMART" @selected($lead->platform === 'IndiaMART')>IndiaMART</option>
                    <option value="Others" @selected($lead->platform === 'Others')>Others</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lead Date & Time</label>
                <input type="datetime-local" name="lead_date" value="{{ \Carbon\Carbon::parse($lead->lead_date)->format('Y-m-d\TH:i') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Name</label>
                <input type="text" name="buyer_name" value="{{ $lead->buyer_name }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Location</label>
                <input type="text" name="buyer_location" value="{{ $lead->buyer_location }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Contact</label>
                <input type="text" name="buyer_contact" value="{{ $lead->buyer_contact }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Searched</label>
                <input type="text" name="platform_keyword" value="{{ $lead->platform_keyword }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Details (Product name; Quanity; Price/Unit)</label>
                <textarea name="product_detail" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ $lead->product_detail }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Location</label>
                <input type="text" name="delivery_location" value="{{ $lead->delivery_location }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expected Delivery Date</label>
                <input type="date" name="expected_delivery_date" id="expected_delivery_date"
                       value="{{ $lead->expected_delivery_date }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <p id="delivery_days_left" class="text-xs text-gray-600 mt-1"></p>
            </div>


            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                <textarea name="remarks" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ $lead->remarks }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Follow Up Date</label>
                <input type="date" name="follow_up_date" id="follow_up_date"
                       value="{{ $lead->follow_up_date }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2">
                <p id="followup_days_left" class="text-xs text-gray-600 mt-1"></p>
            </div>


            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @foreach(['New Lead', 'Lead Followup', 'In Progress', 'Quotation', 'PO', 'Cancelled', 'Completed'] as $status)
                        <option value="{{ $status }}" @selected($lead->status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                <select name="assigned_to" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    @foreach($users as $userId => $userName)
                        <option value="{{ $userName }}" @selected($lead->assigned_to === $userName || (empty($lead->assigned_to) && $userName === $currentUser))>
                            {{ $userName }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-8 gap-4">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg w-full sm:w-auto text-sm">
                Save Changes
            </button>
            <a href="{{ route('leads.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 underline">
                Cancel
            </a>
        </div>
    </form>
</div>
</div>
<script>
    function calculateDays(idInput, idOutput) {
        const input = document.getElementById(idInput);
        const output = document.getElementById(idOutput);

        const updateText = () => {
            const date = new Date(input.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Remove time for accurate diff

            if (!isNaN(date.getTime())) {
                const diffTime = date - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                output.textContent = diffDays >= 0
                    ? `${diffDays} day(s) from today`
                    : `${Math.abs(diffDays)} day(s) ago`;
            } else {
                output.textContent = '';
            }
        };

        input.addEventListener('input', updateText);
        updateText(); // call once on page load
    }

    calculateDays('expected_delivery_date', 'delivery_days_left');
    calculateDays('follow_up_date', 'followup_days_left');
</script>
</x-app-layout>
