<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Ticket - Emergency Department</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .ticket { 
                box-shadow: none; 
                border: 2px solid #ddd;
                page-break-inside: avoid;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
        }
        
        .ticket {
            background: white;
            width: 300px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        
        .ticket-header {
            margin-bottom: 25px;
        }
        
        .ticket-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .queue-number {
            font-size: 72px;
            font-weight: bold;
            color: #2563eb;
            margin: 20px 0;
            line-height: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .ticket-id {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
        }
        
        .department {
            font-size: 18px;
            color: #444;
            font-weight: 600;
            margin: 20px 0;
            padding: 12px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            border-left: 4px solid #dc3545;
        }
        
        .timestamp {
            font-size: 12px;
            color: #888;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px dashed #ddd;
        }
        
        .instructions {
            font-size: 11px;
            color: #666;
            margin-top: 15px;
            line-height: 1.4;
            font-style: italic;
        }
        
        .print-button {
            margin: 20px 0;
            padding: 12px 24px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .print-button:hover {
            background: #1d4ed8;
        }
        
        .emergency-icon {
            color: #dc3545;
            font-size: 20px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="ticket-header">
            <div class="ticket-title">Queue Ticket</div>
        </div>
        
        <div class="queue-number">{{  $queue->number }}</div>
        
        <div class="ticket-id">{{  $queue->reference_number }}</div>
        
        <div class="department">{{  $queue->dept }}</div>
        
        <div class="timestamp">
            {{  Carbon\Carbon::parse($queue->created_at)->toDayDateTimeString() }}
        </div>
    </div>
    
    {{-- <div class="no-print" style="position: absolute; top: 20px; right: 20px;">
        <button class="print-button" onclick="window.print()">🖨️ Print Ticket</button>
    </div> --}}
    
    <script>
        // Auto-focus for better printing experience
        window.onload = function() {
            // Add any additional JavaScript functionality here if needed
        };
    </script>
</body>
</html>