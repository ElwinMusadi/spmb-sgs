<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Registration fee line items in IDR.
     *
     * @var array<int, array{description: string, detail: string, amount: int}>
     */
    private const LINE_ITEMS = [
        [
            'description' => 'Registration Fee',
            'detail' => 'Annual registration fee',
            'amount' => 500000,
        ],
        [
            'description' => 'Entrance Assessment Test',
            'detail' => 'Computer-based cognitive assessment',
            'amount' => 250000,
        ],
    ];

    private const ADMIN_FEE = 4500;

    /**
     * Download the registration invoice for the given application as a PDF.
     */
    public function download(Application $application): Response
    {
        abort_unless($application->user_id === Auth::id(), 403);

        $application->loadMissing(['schoolLevel', 'academicYear']);

        $subtotal = array_sum(array_column(self::LINE_ITEMS, 'amount'));
        $total = $subtotal + self::ADMIN_FEE;

        $invoiceNumber = sprintf(
            'INV-SGS-%s-%s',
            $application->created_at?->format('Y') ?? now()->format('Y'),
            str_pad((string) $application->id, 4, '0', STR_PAD_LEFT),
        );

        $pdf = Pdf::loadView('pdf.invoice', [
            'application' => $application,
            'invoiceNumber' => $invoiceNumber,
            'lineItems' => self::LINE_ITEMS,
            'adminFee' => self::ADMIN_FEE,
            'subtotal' => $subtotal,
            'total' => $total,
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
