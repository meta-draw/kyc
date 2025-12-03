<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('id_card', 18);
            $table->string('mobile', 11);
            $table->string('real_name');
            $table->boolean('status')->default(false)->index();
            $table->string('reason')->nullable();
            $table->timestamps();
            
            $table->index(['id_card', 'mobile']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};