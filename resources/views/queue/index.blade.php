@extends('queue.layout')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Queue Dashboard</h2>
        <div class="space-x-2">
            <a href="{{ route('queue.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                New Queue
            </a>
            <form method="POST" action="{{ route('queue.reset') }}" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600" 
                        onclick="return confirm('Are you sure you want to reset all queues?')">
                    Reset Today's Queues
                </button>
            </form>
        </div>
    </div>

    {{-- Department Filter --}}
    <div class="mb-4">
        <form method="GET" class="flex items-center space-x-2">
            <label class="font-medium">Department:</label>
            <select name="dept" class="border rounded px-3 py-1" onchange="this.form.submit()">
                <option value="all" {{ $dept === 'all' ? 'selected' : '' }}>All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" {{ $dept === $department ? 'selected' : '' }}>
                        {{ $department }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-100 p-4 rounded-lg">
            <h3 class="font-semibold text-blue-800">Waiting</h3>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['waiting'] }}</p>
        </div>
        <div class="bg-yellow-100 p-4 rounded-lg">
            <h3 class="font-semibold text-yellow-800">Serving</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['serving'] }}</p>
        </div>
        <div class="bg-green-100 p-4 rounded-lg">
            <h3 class="font-semibold text-green-800">Completed</h3>
            <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-red-100 p-4 rounded-lg">
            <h3 class="font-semibold text-red-800">Cancelled</h3>
            <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</p>
        </div>
    </div>

    {{-- Call Next Buttons --}}
    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Call Next Queue</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($departments as $department)
                @php
                    $waitingCount = $queues->where('dept', $department)->where('status', 'waiting')->count();
                @endphp
                <button onclick="callNext('{{ $department }}')" 
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 {{ $waitingCount == 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ $waitingCount == 0 ? 'disabled' : '' }}>
                    {{ $department }} ({{ $waitingCount }})
                </button>
            @endforeach
        </div>
    </div>

    {{-- Queue Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($queues as $queue)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('queue.show', $queue) }}" class="text-blue-600 hover:underline">
                                {{ $queue->reference_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $queue->number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $queue->dept }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $queue->status === 'waiting' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $queue->status === 'serving' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $queue->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $queue->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($queue->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $queue->created_at->format('H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            @if($queue->status === 'waiting')
                                <button onclick="updateStatus({{ $queue->id }}, 'serving')" 
                                        class="text-yellow-600 hover:text-yellow-900">Serve</button>
                                <button onclick="updateStatus({{ $queue->id }}, 'cancelled')" 
                                        class="text-red-600 hover:text-red-900">Cancel</button>
                            @elseif($queue->status === 'serving')
                                <button onclick="updateStatus({{ $queue->id }}, 'completed')" 
                                        class="text-green-600 hover:text-green-900">Complete</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No queues found for today
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function callNext(dept) {
    $.post('{{ route('queue.call-next') }}', { dept: dept })
        .done(function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message);
            }
        })
        .fail(function() {
            alert('Error calling next queue');
        });
}

function updateStatus(queueId, status) {
    $.ajax({
        url: `/queue/${queueId}/status`,
        method: 'PATCH',
        data: { status: status }
    })
    .done(function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert('Error updating status');
        }
    })
    .fail(function() {
        alert('Error updating status');
    });
}

// Auto-refresh every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>
@endsection