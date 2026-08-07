<?php

namespace App\Models;

use Database\Factories\RequiredDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $document_type
 * @property string $name
 * @property string|null $description
 * @property array<int, string> $allowed_extensions
 * @property bool $is_required
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['document_type', 'name', 'description', 'allowed_extensions', 'is_required', 'is_active', 'sort_order'])]
class RequiredDocument extends Model
{
    /** @use HasFactory<RequiredDocumentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'allowed_extensions' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function extensionString(): string
    {
        return collect($this->allowed_extensions)->map(fn (string $extension): string => strtoupper($extension))->implode(', ');
    }
}
