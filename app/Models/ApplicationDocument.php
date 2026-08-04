<?php

namespace App\Models;

use Database\Factories\ApplicationDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $application_id
 * @property string $document_type
 * @property string $file_path
 * @property string $file_name
 * @property int $file_size
 * @property bool $is_required
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[
    Fillable([
        'application_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'is_required',
    ]),
]
class ApplicationDocument extends Model
{
    /** @use HasFactory<ApplicationDocumentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    /**
     * Get the application that owns this document.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
