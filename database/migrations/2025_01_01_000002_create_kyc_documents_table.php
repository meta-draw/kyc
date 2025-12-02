<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('kyc.table_prefix', 'kyc_') . 'documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')
                ->constrained(config('kyc.table_prefix', 'kyc_') . 'verifications')
                ->cascadeOnDelete();
            $table->enum('type', ['id-front', 'id-back']);
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->unsignedInteger('file_size');
            $table->timestamps();
            
            $table->index(['verification_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('kyc.table_prefix', 'kyc_') . 'documents');
    }
};