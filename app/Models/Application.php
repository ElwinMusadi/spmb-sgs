<?php

namespace App\Models;

use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $school_level_id
 * @property int|null $academic_year_id
 * @property string|null $grade_level
 * @property string $status
 * @property int $current_step
 * @property string $payment_status
 * @property string|null $payment_method
 * @property Carbon|null $paid_at
 * @property string|null $student_full_name
 * @property string|null $student_nik
 * @property string|null $student_nisn
 * @property string|null $student_birth_place
 * @property Carbon|null $student_birth_date
 * @property string|null $student_gender
 * @property string|null $student_religion
 * @property string|null $student_citizenship
 * @property string|null $student_address
 * @property string|null $student_previous_school
 * @property string|null $father_name
 * @property string|null $father_phone
 * @property string|null $father_email
 * @property string|null $father_occupation
 * @property string|null $father_address
 * @property string|null $mother_name
 * @property string|null $mother_phone
 * @property string|null $mother_email
 * @property string|null $mother_occupation
 * @property string|null $mother_address
 * @property string|null $guardian_name
 * @property string|null $guardian_phone
 * @property string|null $guardian_email
 * @property string|null $guardian_occupation
 * @property string|null $guardian_address
 * @property Carbon|null $submitted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[
    Fillable([
        'user_id',
        'school_level_id',
        'academic_year_id',
        'grade_level',
        'status',
        'current_step',
        'payment_status',
        'payment_method',
        'paid_at',
        'student_full_name',
        'student_nik',
        'student_nisn',
        'student_birth_place',
        'student_birth_date',
        'student_gender',
        'student_religion',
        'student_citizenship',
        'student_address',
        'student_previous_school',
        'father_name',
        'father_phone',
        'father_email',
        'father_occupation',
        'father_address',
        'mother_name',
        'mother_phone',
        'mother_email',
        'mother_occupation',
        'mother_address',
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'guardian_occupation',
        'guardian_address',
        'submitted_at',
    ]),
]
class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'student_birth_date' => 'date',
            'submitted_at' => 'datetime',
            'paid_at' => 'datetime',
            'current_step' => 'integer',
        ];
    }

    /**
     * Get the user that owns this application.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the school level for this application.
     */
    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }

    /**
     * Get the academic year for this application.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the documents for this application.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    /**
     * Check if the application is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the application has been submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if the registration payment has been settled.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
