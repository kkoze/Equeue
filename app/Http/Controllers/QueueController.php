<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
    // Display queue dashboard
    public function index(Request $request)
    {
        $dept = $request->get('dept', 'all');
        
        $query = Queue::today()->orderBy('created_at', 'asc');
        
        if ($dept !== 'all') {
            $query->department($dept);
        }
        
        $queues = $query->get();
        
        $departments = Queue::select('dept')
                           ->distinct()
                           ->pluck('dept')
                           ->sort();
        
        $stats = [
            'waiting' => $queues->where('status', 'waiting')->count(),
            'serving' => $queues->where('status', 'serving')->count(),
            'completed' => $queues->where('status', 'completed')->count(),
            'cancelled' => $queues->where('status', 'cancelled')->count(),
        ];
        
        return view('queue.index', compact('queues', 'departments', 'dept', 'stats'));
    }

    // Show queue creation form
    public function create()
    {
        $departments = ['Customer Service', 'Technical Support', 'Billing', 'Sales'];
        return view('queue.create', compact('departments'));
    }

    // Store new queue
    public function store(Request $request)
    {
        $dept = "Emergency Department";
        $referenceNumber = Queue::generateReferenceNumber($dept);
        $number = Queue::getNextNumber($dept);

        $queue = Queue::create([
            'reference_number' => $referenceNumber,
            'number' => $number,
            'dept' => $dept,
            'status' => 'waiting'
        ]);

        Log::info('New queue created', [
            'reference_number' => $queue->reference_number,
            'number' => $queue->number,
            'dept' => $queue->dept
        ]);
        dispatch(new \App\Jobs\RawPrintQueueTicket($queue->id)); // Print the queue ticket immediately
        
        return redirect()->route('queue.show', $queue)
                        ->with('success', 'Queue ticket created successfully!');
    }

    // Show specific queue
    public function show(Queue $queue)
    {
        $position = Queue::where('dept', $queue->dept)
                         ->where('status', 'waiting')
                         ->where('created_at', '<', $queue->created_at)
                         ->count() + 1;

        $currentServing = Queue::where('dept', $queue->dept)
                              ->serving()
                              ->first();

        return view('queue.show', compact('queue', 'position', 'currentServing'));
    }

    // Call next queue
    public function callNext(Request $request)
    {
        $dept = $request->dept;
        
        // Mark current serving as completed
        $currentServing = Queue::where('dept', $dept)->serving()->first();
        if ($currentServing) {
            $currentServing->markAsCompleted();
        }
        
        // Get next waiting queue
        $nextQueue = Queue::where('dept', $dept)
                          ->waiting()
                          ->orderBy('created_at', 'asc')
                          ->first();
        
        if ($nextQueue) {
            $nextQueue->markAsServing();
            return response()->json([
                'success' => true,
                'queue' => $nextQueue,
                'message' => "Now serving: {$nextQueue->reference_number}"
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No waiting queues found'
        ]);
    }

    // Update queue status
    public function updateStatus(Queue $queue, Request $request)
    {
        $status = $request->status;
        
        switch ($status) {
            case 'serving':
                $queue->markAsServing();
                break;
            case 'completed':
                $queue->markAsCompleted();
                break;
            case 'cancelled':
                $queue->markAsCancelled();
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Queue status updated to {$status}"
        ]);
    }

    // Reset daily queues (for testing)
    public function reset()
    {
        Queue::whereDate('created_at', today())->delete();
        
        return redirect()->route('queue.index')
                        ->with('success', 'Today\'s queues have been reset');
    }

    public function print()
    {
        $queue = Queue::findOrFail(32);

        return view('queue.print.queue', compact('queue'));
    }
}