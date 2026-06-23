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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            // Optional Blade view path (e.g. "emails.customer.tickets")
            // If null/empty, the system falls back to the default Blade view.
            $table->string('view_name')->nullable();
            // Optional raw HTML body. Only used when no view_name is provided.
            $table->longText('html_content')->nullable();
            $table->text('description')->nullable();
            // Only one template can be active at a time.
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
