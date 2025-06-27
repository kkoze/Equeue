<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Ticket - Emergency Department</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        @media print {
            html, body { 
                margin: 0; 
                padding: 0;
                width: 100vw;
                height: 100vh;
            }
            .no-print { display: none; }
            .ticket { 
                box-shadow: none; 
                border: none;
                page-break-inside: avoid;
                width: 100vw;
                height: 100vh;
                margin: 0;
                padding: 40px;
                transform: none;
            }
            .queue-number {
                font-size: 120px;
                margin: 40px 0;
            }
            .ticket-title {
                font-size: 32px;
                margin-bottom: 30px;
            }
            .department {
                font-size: 28px;
                padding: 20px;
                margin: 30px 0;
            }
            .ticket-id {
                font-size: 24px;
                padding: 15px 25px;
                margin-bottom: 30px;
            }
            .timestamp {
                font-size: 20px;
                margin-top: 40px;
                padding-top: 30px;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .ticket {
            background: white;
            width: 100vw;
            height: 100vh;
            padding: 40px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            position: relative;
        }
        
        .ticket-header {
            margin-bottom: 40px;
        }
        
        .ticket-title {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .queue-number {
            font-size: 150px;
            font-weight: bold;
            color: #2563eb;
            margin: 50px 0;
            line-height: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .ticket-id {
            font-size: 28px;
            color: #666;
            margin-bottom: 40px;
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 15px 30px;
            border-radius: 8px;
            display: inline-block;
        }
        
        .department {
            font-size: 32px;
            color: #444;
            font-weight: 600;
            margin: 40px 0;
            padding: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            border-left: 8px solid #dc3545;
            min-width: 300px;
        }
        
        .timestamp {
            font-size: 24px;
            color: #888;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px dashed #ddd;
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .print-button {
            margin: 20px 0;
            padding: 15px 30px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s;
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #1d4ed8;
        }
        
        /* Screen preview scaling */
        @media screen and (max-width: 1200px) {
            .ticket {
                transform: scale(0.7);
                transform-origin: center;
            }
        }
        
        @media screen and (max-width: 800px) {
            .ticket {
                transform: scale(0.5);
                transform-origin: center;
            }
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
    
    <div class="no-print">
        <button class="print-button" onclick="window.print()">🖨️ Print as PDF</button>
    </div>
    
    <script>
        // Auto-focus for better printing experience
        window.onload = function() {
            // Add any additional JavaScript functionality here if needed
        };
    </script>
</body>
</html>