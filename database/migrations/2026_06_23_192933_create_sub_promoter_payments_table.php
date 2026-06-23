<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Records every "debt payment" event in the promoter hierarchy.
     *
     * Two kinds of payment are tracked:
     *
     *   1. 'sub_to_manager'  — a sub-promoter pays the promoter-manager the
     *                          amount he owes from the orders he placed
     *                          (gross ticket revenue minus the sub's own
     *                          commission).
     *
     *   2. 'manager_to_organizers' — a promoter-manager records that he
     *                          has forwarded his collected revenue to the
     *                          event organizers (the amount owed after
     *                          subtracting his own commission and every
     *                          sub-promoter's commission).
     *
     * The schema is generic enough to also support a 'sub_to_organizers'
     * row (orphan sub-promoter paying organizers directly) but the app
     * does not generate that case today.
     *
     * Each row stores:
     *   - payer_id / receiver_id — the two users involved.
     *   - amount                 — money in RSD the payer is acknowledging
     *                              he has transferred to the receiver.
     *   - note                   — optional free-text remark.
     *   - recorded_by            — who logged the payment. For
     *                              sub_to_manager this is normally the
     *                              promoter-manager (he received the cash)
     *                              but a sub-promoter can also self-log a
     *                              payment on his own behalf.
     *   - paid_at                — the date of the transfer (defaults to
     *                              now; editable so back-dated entries are
     *                              possible).
     */
    public function up(): void
    {
        Schema::create('sub_promoter_payments', function (Blueprint $table) {
            $table->id();

            $table->enum('payment_type', [
                'sub_to_manager',
                'manager_to_organizers',
            ]);

            $table->foreignId('payer_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('receiver_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);

            $table->string('note', 500)->nullable();

            $table->foreignId('recorded_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamp('paid_at')->useCurrent();

            $table->timestamps();

            $table->index(['payment_type', 'payer_id']);
            $table->index(['payment_type', 'receiver_id']);
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_promoter_payments');
    }
};
