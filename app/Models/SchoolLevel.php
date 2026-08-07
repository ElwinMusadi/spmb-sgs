<?php

namespace App\Models;

use Database\Factories\SchoolLevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property bool $is_active
 */
#[Fillable(['name', 'code', 'description', 'is_active'])]
class SchoolLevel extends Model
{
    /** @use HasFactory<SchoolLevelFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the applications for this school level.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class)->orderBy('sort_order');
    }
}
