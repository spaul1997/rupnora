<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name', 120);
            $table->string('email')->index();
            $table->string('phone', 20);
            $table->string('location', 120);
            $table->string('area_of_interest')->index();
            $table->string('current_role', 150)->nullable();
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('portfolio_url', 500)->nullable();
            $table->text('message');
            $table->string('cv_path');
            $table->string('cv_original_name');
            $table->string('cv_mime_type', 150);
            $table->unsignedBigInteger('cv_size');
            $table->string('status', 30)->default('new')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_applications');
    }
};
