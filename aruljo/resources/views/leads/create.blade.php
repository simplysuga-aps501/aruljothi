<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">
            Create Lead
        </h2>
    </x-slot>
 <div class="py-6 px-4 sm:px-6 lg:px-8">
     <div class="max-w-4xl mx-auto bg-white shadow-md rounded-xl p-6">
         @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('leads.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <!-- Lead Platform -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lead Platform</label>
                    <select name="platform" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" required>
                        <option value="">Select</option>
                        <option value="Just Dial">Just Dial</option>
                        <option value="India Mart">India Mart</option>
                        <option value="Others">Others</option>
                    </select>
                </div>

                <!-- Lead Date & Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lead Date & Time</label>
                    <input type="datetime-local" name="lead_date" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}" required>
                </div>

                <!-- Buyer Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Name</label>
                    <input type="text" name="buyer_name" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>

                <!-- Buyer Location -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Location</label>
                    <input type="text" name="buyer_location" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Buyer Contact -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Contact Number</label>
                    <input type="text" name="buyer_contact"
                           oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                           maxlength="10"
                           pattern="[6-9]{1}[0-9]{9}"
                           title="Enter a valid 10-digit Indian mobile number starting with 6-9"
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>

                </div>

                <!-- Item Searched -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Searched</label>
                    <input type="text" name="platform_keyword" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex justify-center sm:justify-start gap-4 pt-4">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl text-base transition duration-200">
                    Submit
                </button>

                <a href="{{ route('leads.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-xl text-base transition duration-200">
                    ← Back
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
