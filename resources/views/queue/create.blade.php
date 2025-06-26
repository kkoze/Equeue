@extends('queue.layout')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold mb-6 text-center">Generate Number</h2>
    
    <form method="POST" action="{{ route('queue.store') }}">
        @csrf
        
        <button type="submit" 
                class="w-full bg-green-500 text-white font-bold py-3 px-4 rounded-lg hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
            Generate
        </button>
    </form>
</div>

<!-- Queue Ticket Print Page -->
@if(session('queue_ticket'))
<div id="printableTicket" class="fixed inset-0 bg-white flex items-center justify-center z-50">
    <div class="bg-white p-8 max-w-sm w-full text-center print-ticket">
        <!-- Ticket Header -->
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Queue Ticket</h1>
        
        <!-- Large Number Display -->
        <div class="mb-6">
            <div class="text-8xl font-bold text-blue-600 mb-4">
                {{ session('queue_ticket')->queue_number }}
            </div>
        </div>
        
        <!-- Ticket Details -->
        <div class="space-y-3 text-gray-700 mb-6">
            <div class="text-lg font-medium">
                {{ session('queue_ticket')->ticket_id }}
            </div>
            <div class="text-base">
                {{ session('queue_ticket')->department }}
            </div>
        </div>
        
        <!-- Timestamp -->
        <div class="pt-4 border-t border-gray-300">
            <div class="text-sm text-gray-500">
                Created: {{ session('queue_ticket')->created_at->format('M d, Y H:i:s') }}
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printableTicket, #printableTicket * {
        visibility: visible;
    }
    #printableTicket {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: white !important;
    }
    .print-ticket {
        border: 2px solid #000;
        max-width: 300px;
        margin: 0 auto;
        padding: 20px;
    }
}

@media screen {
    #printableTicket {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .print-ticket {
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
}
</style>

<script>
// Auto-print when ticket is generated
window.onload = function() {
    window.print();
    // Redirect back after printing (optional)
    setTimeout(function() {
        window.location.href = "{{ route('queue.create') }}";
    }, 1000);
}
</script>
@endif
@endsection