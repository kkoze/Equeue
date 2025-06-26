@extends('queue.layout')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-8 text-center">
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Queue Ticket</h2>
        
        <div class="mb-5">
            <div class="text-6xl font-bold text-blue-600 mb-2">{{ $queue->number }}</div>
            <div class="text-xl text-gray-600 mb-4">{{ $queue->reference_number }}</div>
            <div class="text-lg font-semibold text-gray-700">{{ $queue->dept }}</div>
        </div>
        
        <div class="border-t border-gray-200">
            {{-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-gray-700 mb-2">Status</h3>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full 
                        {{ $queue->status === 'waiting' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $queue->status === 'serving' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $queue->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $queue->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($queue->status) }}
                    </span>
                </div>
                
                @if($queue->status === 'waiting')
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Position in Queue</h3>
                        <div class="text-2xl font-bold text-orange-600">{{ $position }}</div>
                    </div>
                @endif
            </div>
            
            @if($currentServing)
                <div class="mt-6 p-4 bg-yellow-50 rounded-lg">
                    <h3 class="font-semibold text-yellow-800 mb-2">Currently Serving</h3>
                    <div class="text-lg font-bold text-yellow-700">
                        {{ $currentServing->reference_number }} (Number: {{ $currentServing->number }})
                    </div>
                </div>
            @endif --}}
            
            <div class="mt-6 text-sm text-gray-500">
                <p>Created: {{ $queue->created_at->format('M d, Y H:i:s') }}</p>
                @if($queue->served_at)
                    <p>Served: {{ $queue->served_at->format('M d, Y H:i:s') }}</p>
                @endif
                @if($queue->completed_at)
                    <p>Completed: {{ $queue->completed_at->format('M d, Y H:i:s') }}</p>
                @endif
            </div>
        </div>
        
        <div class="mt-8">
            <div class="text-lg text-gray-700">
                Redirecting in <span id="countdown" class="font-bold text-green-600">10</span> seconds...
            </div>
        </div>
    </div>
</div>

<script>
    let countdown = 10;
    const countdownElement = document.getElementById('countdown');
    
    const timer = setInterval(function() {
        countdown--;
        countdownElement.textContent = countdown;
        
        if (countdown <= 0) {
            clearInterval(timer);
            window.location.href = "{{ route('queue.create') }}";
        }
    }, 1000);
</script>
@endsection