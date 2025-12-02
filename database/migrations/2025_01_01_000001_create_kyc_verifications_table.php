<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('kyc.table_prefix', 'kyc_') . 'verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('nationality', 2);
            $table->string('resident_country', 2);
            $table->date('dob');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('document_type');
            $table->string('country_of_issue', 2);
            $table->string('document_number');
            $table->date('document_issue_date');
            $table->date('document_expiry_date');
            $table->string('id_front_url')->nullable();
            $table->string('id_back_url')->nullable();
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('document_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('kyc.table_prefix', 'kyc_') . 'verifications');
    }
};