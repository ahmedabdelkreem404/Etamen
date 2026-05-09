<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->foreignId('hospital_provider_id')
                ->nullable()
                ->after('provider_id')
                ->constrained('providers')
                ->nullOnDelete();
            $table->foreignId('hospital_department_id')
                ->nullable()
                ->after('hospital_provider_id')
                ->constrained('hospital_departments')
                ->nullOnDelete();
            $table->foreignId('hospital_doctor_id')
                ->nullable()
                ->after('hospital_department_id')
                ->constrained('hospital_doctors')
                ->nullOnDelete();

            $table->index('hospital_provider_id', 'appointments_hospital_provider_idx');
            $table->index('hospital_department_id', 'appointments_hospital_department_idx');
            $table->index('hospital_doctor_id', 'appointments_hospital_doctor_idx');
            $table->index(['hospital_provider_id', 'status'], 'appointments_hospital_status_idx');
            $table->index(['hospital_provider_id', 'booked_at'], 'appointments_hospital_booked_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex('appointments_hospital_provider_idx');
            $table->dropIndex('appointments_hospital_department_idx');
            $table->dropIndex('appointments_hospital_doctor_idx');
            $table->dropIndex('appointments_hospital_status_idx');
            $table->dropIndex('appointments_hospital_booked_at_idx');

            $table->dropConstrainedForeignId('hospital_doctor_id');
            $table->dropConstrainedForeignId('hospital_department_id');
            $table->dropConstrainedForeignId('hospital_provider_id');
        });
    }
};
