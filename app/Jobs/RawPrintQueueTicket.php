<?php

namespace App\Jobs;

use App\Models\Queue;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\Process\Process;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class RawPrintQueueTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $queueId) {}

    public function handle(): void
    {
        Log::info('Starting print job for queue ticket', [
            'queue_id' => $this->queueId
        ]);
        
        try {
            $queue = Queue::findOrFail($this->queueId);

            // Generate PDF using Browsershot
            $pdfBase64 = Browsershot::html(
                view('queue.print.queue', compact('queue'))->render()
            )
            ->paperSize(58, 100, 'mm')
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->base64pdf();

            $pdfBinary = base64_decode($pdfBase64);

            Log::info('PDF generated for queue ticket', [
                'queue_id' => $this->queueId,
                'reference_number' => $queue->reference_number,
                'number' => $queue->number,
                'dept' => $queue->dept,
                'pdf_size' => strlen($pdfBinary) . ' bytes'
            ]);

            // Print using Ghostscript (Windows)
            $this->printWithGhostscript($pdfBinary);

            Log::info('Queue ticket printed successfully', [
                'queue_id' => $this->queueId,
                'reference_number' => $queue->reference_number
            ]);

        } catch (Exception $e) {
            Log::error('Failed to print queue ticket', [
                'queue_id' => $this->queueId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw to mark job as failed
            throw $e;
        }
    }

    private function printWithGhostscript(string $pdfBinary): void
    {
        $gsPath = '"C:\Program Files\gs\gs10.02.1\bin\gswin64c.exe"';
        $printerName = '\\\\IT-Techsupport\\IT-TECHSUPPORT';
        
        $command = sprintf(
            '%s -dBATCH -dNOPAUSE -sDEVICE=mswinpr2 -sOutputFile="%%printer%%%s" -',
            $gsPath,
            $printerName
        );

        $process = Process::fromShellCommandline(
            $command,
            null, // cwd
            null, // env
            $pdfBinary, // input
            60 // timeout in seconds
        );

        $process->mustRun();
    }

    /**
     * Alternative method for network printer (raw socket)
     */
    private function printWithRawSocket(string $pdfBinary, string $printerIp = '192.168.1.35', int $port = 9100): void
    {
        $socket = fsockopen($printerIp, $port, $errno, $errstr, 30);
        
        if (!$socket) {
            throw new Exception("Failed to connect to printer: $errstr ($errno)");
        }

        $bytesWritten = fwrite($socket, $pdfBinary);
        fclose($socket);

        if ($bytesWritten === false) {
            throw new Exception('Failed to write data to printer');
        }

        Log::info('Data sent to network printer', [
            'printer_ip' => $printerIp,
            'port' => $port,
            'bytes_sent' => $bytesWritten
        ]);
    }
}