<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\PaymentFeeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Download the registration invoice for the given application as a PDF.
     */
    public function download(Application $application): Response
    {
        abort_unless($application->user_id === Auth::id(), 403);

        $application->loadMissing(['schoolLevel', 'academicYear']);
        $feeService = app(PaymentFeeService::class);
        $fees = $feeService->feesFor($application);
        $totals = $feeService->totals($fees);

        $invoiceNumber = sprintf(
            'INV-SGS-%s-%s',
            $application->created_at?->format('Y') ?? now()->format('Y'),
            str_pad((string) $application->id, 4, '0', STR_PAD_LEFT),
        );

        $pdf = Pdf::loadView('pdf.invoice', [
            'application' => $application,
            'invoiceNumber' => $invoiceNumber,
            'lineItems' => $fees->map(fn ($fee): array => [
                'description' => $fee->name,
                'detail' => $fee->description ?? '',
                'amount' => $fee->amount,
            ])->all(),
            'adminFee' => $totals['admin_fee'],
            'subtotal' => $totals['subtotal'],
            'total' => $totals['total'],
            'issuedAt' => $application->created_at ?? now(),
            'dueAt' => ($application->created_at ?? now())->copy()->addDays(7),
            'logoData' => $this->brandLogoData(),
        ])->setPaper('a4');

        return $pdf->download($invoiceNumber.'.pdf');
    }

    /**
     * Get the brand logo as a base64 data URI for embedding in the PDF.
     */
    private function brandLogoData(): ?string
    {
        $path = public_path('images/logo-stella-gracia.png');

        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }
}
