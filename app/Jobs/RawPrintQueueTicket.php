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
use Illuminate\Support\Facades\Storage;
use Exception;

class RawPrintQueueTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $queueId) {}

    public function handle(): void
    {
        Log::info('Starting PDF generation and print for queue ticket', [
            'queue_id' => $this->queueId
        ]);
        
        try {
            $queue = Queue::findOrFail($this->queueId);

            // Define the PDF filename in Laravel storage
            $filename = "queue_tickets/queue_{$this->queueId}.pdf";
            
            // Create the queue_tickets directory in public storage
            $storageDir = storage_path('app/public/queue_tickets');
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            // Define the full path where PDF will be saved
            $pdfPath = $storageDir . "/queue_{$this->queueId}.pdf";
            
            // Generate PDF directly to storage location using Browsershot
            Browsershot::html(
                view('queue.print.queue', compact('queue'))->render()
            )
            ->paperSize(100, 150, 'mm')
            ->waitUntilNetworkIdle()
            ->scale(1.0)
            ->save($pdfPath);

            // Verify the PDF was saved
            if (!file_exists($pdfPath)) {
                throw new Exception("Failed to save PDF to storage: {$pdfPath}");
            }

            // Get file size for logging
            $fileSize = filesize($pdfPath);

            Log::info('PDF generated and saved successfully', [
                'queue_id' => $this->queueId,
                'reference_number' => $queue->reference_number,
                'number' => $queue->number,
                'dept' => $queue->dept,
                'pdf_size' => $fileSize . ' bytes',
                'storage_path' => "queue_tickets/queue_{$this->queueId}.pdf",
                'full_path' => $pdfPath,
                'file_exists' => file_exists($pdfPath)
            ]);

            // Print the PDF from Laravel storage
            $this->printPdf($pdfPath);

            Log::info('Queue ticket PDF generated and printed successfully', [
                'queue_id' => $this->queueId,
                'reference_number' => $queue->reference_number,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to generate/print queue ticket PDF', [
                'queue_id' => $this->queueId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function printPdf(string $pdfFilePath): void
    {
        // First try Ghostscript method
        try {
            $this->printWithGhostscript($pdfFilePath);
            return;
        } catch (Exception $e) {
            Log::warning('Ghostscript printing failed, trying Windows print command', [
                'queue_id' => $this->queueId,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback to Windows print command
        try {
            $this->printWithWindowsPrinter($pdfFilePath);
            return;
        } catch (Exception $e) {
            Log::warning('Windows print command failed, trying raw socket', [
                'queue_id' => $this->queueId,
                'error' => $e->getMessage()
            ]);
        }

        // Last resort: raw socket printing
        $this->printWithRawSocket($pdfFilePath);
    }

    private function printWithGhostscript(string $pdfFilePath): void
    {
        // Try multiple possible Ghostscript paths
        $possiblePaths = [
            'C:\Program Files\gs\gs10.02.1\bin\gswin64c.exe',
            'C:\Program Files\gs\gs10.03.1\bin\gswin64c.exe',
            'C:\Program Files\gs\gs10.04.0\bin\gswin64c.exe',
            'C:\Program Files (x86)\gs\gs10.02.1\bin\gswin32c.exe',
            'C:\Program Files (x86)\gs\gs10.03.1\bin\gswin32c.exe',
            'gswin64c.exe', // If it's in PATH
            'gswin32c.exe'  // If it's in PATH
        ];

        $gsPath = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path) || (!str_contains($path, ':') && !str_contains($path, '\\'))) {
                $gsPath = '"' . $path . '"';
                break;
            }
        }

        if (!$gsPath) {
            throw new Exception("Ghostscript not found. Please install Ghostscript or check the installation path.");
        }

        $printerName = 'Thermal Printer';
        
        // Ensure the PDF file exists
        if (!file_exists($pdfFilePath)) {
            throw new Exception("PDF file not found: {$pdfFilePath}");
        }
        
        Log::info('Printing PDF with Ghostscript', [
            'queue_id' => $this->queueId,
            'gs_path' => $gsPath,
            'pdf_file' => $pdfFilePath,
            'file_size' => filesize($pdfFilePath) . ' bytes'
        ]);
        
        // Build Ghostscript command with proper escaping
        $command = sprintf(
            '%s -dBATCH -dNOPAUSE -sDEVICE=mswinpr2 -sOutputFile="% %s" "%s"',
            $gsPath,
            $printerName,
            $pdfFilePath
        );
        
        Log::info('Executing Ghostscript command', [
            'queue_id' => $this->queueId,
            'command' => $command
        ]);
        
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->setIdleTimeout(60);
        
        try {
            $process->mustRun();
            
            Log::info('Ghostscript executed successfully', [
                'queue_id' => $this->queueId,
                'output' => $process->getOutput(),
                'exit_code' => $process->getExitCode()
            ]);
            
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            Log::error('Ghostscript process failed', [
                'queue_id' => $this->queueId,
                'command' => $process->getCommandLine(),
                'exit_code' => $process->getExitCode(),
                'output' => $process->getOutput(),
                'error_output' => $process->getErrorOutput()
            ]);
            
            throw new Exception('Ghostscript failed: ' . $e->getMessage());
        }
    }

    private function printWithWindowsPrinter(string $pdfFilePath): void
    {
        $printerName = 'Thermal Printer';
        $command = sprintf('print /D:"%s" "%s"', $printerName, $pdfFilePath);
        
        Log::info('Printing with Windows print command', [
            'queue_id' => $this->queueId,
            'command' => $command
        ]);
        
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(120);
        
        try {
            $process->mustRun();
            
            Log::info('Windows print command executed successfully', [
                'queue_id' => $this->queueId,
                'printer' => $printerName,
                'output' => $process->getOutput()
            ]);
            
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            throw new Exception('Windows print command failed: ' . $e->getMessage());
        }
    }

    private function printWithRawSocket(string $pdfFilePath, string $printerIp = '192.168.1.35', int $port = 9100): void
    {
        $pdfContent = file_get_contents($pdfFilePath);
        
        if ($pdfContent === false) {
            throw new Exception("Failed to read PDF file: {$pdfFilePath}");
        }
        
        Log::info('Attempting to print via raw socket', [
            'queue_id' => $this->queueId,
            'printer_ip' => $printerIp,
            'port' => $port,
            'pdf_file' => $pdfFilePath,
            'data_size' => strlen($pdfContent) . ' bytes'
        ]);
        
        $socket = fsockopen($printerIp, $port, $errno, $errstr, 30);
        
        if (!$socket) {
            throw new Exception("Failed to connect to printer: $errstr ($errno)");
        }

        try {
            $bytesWritten = fwrite($socket, $pdfContent);
            
            if ($bytesWritten === false) {
                throw new Exception('Failed to write data to printer');
            }
            
            Log::info('Data sent to network printer successfully', [
                'queue_id' => $this->queueId,
                'printer_ip' => $printerIp,
                'port' => $port,
                'bytes_sent' => $bytesWritten
            ]);
            
        } finally {
            fclose($socket);
        }
    }

    /**
     * Get the public URL for the generated PDF
     */
    public function getPdfUrl(): string
    {
        $filename = "queue_tickets/queue_{$this->queueId}.pdf";
        return asset('storage/' . $filename);
    }

    /**
     * Check if PDF exists for this queue ticket
     */
    public static function pdfExists(int $queueId): bool
    {
        $filename = "queue_tickets/queue_{$queueId}.pdf";
        return Storage::disk('public')->exists($filename);
    }

    /**
     * Get the storage path for a queue ticket PDF
     */
    public static function getPdfPath(int $queueId): string
    {
        return "queue_tickets/queue_{$queueId}.pdf";
    }
}