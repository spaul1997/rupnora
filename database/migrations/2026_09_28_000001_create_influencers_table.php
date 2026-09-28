<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('influencers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name', 120);
            $table->string('email')->index();
            $table->string('phone', 20);
            $table->string('location', 120);
            $table->string('primary_platform', 30)->index();
            $table->string('social_handle', 120);
            $table->string('profile_url', 500)->nullable();
            $table->unsignedBigInteger('followers_count')->default(0);
            $table->string('content_niche', 150);
            $table->string('portfolio_url', 500)->nullable();
            $table->text('message')->nullable();
            $table->string('profile_image_path')->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->decimal('commission_rate', 5, 2)->nullable();
            $table->string('coupon_code', 50)->nullable()->unique();
            $table->text('admin_notes')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencers');
    }
};
