<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('student_nik', 16)->nullable()->after('student_full_name');
            $table->string('student_nisn', 10)->nullable()->after('student_nik');
            $table->string('student_citizenship')->nullable()->after('student_religion');
            $table->text('student_address')->nullable()->after('student_citizenship');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'student_nik',
                'student_nisn',
                'student_citizenship',
                'student_address',
            ]);
        });
    }
};
