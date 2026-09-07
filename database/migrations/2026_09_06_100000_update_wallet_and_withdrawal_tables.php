<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update users table for unsigned wallet_balance and enum status
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('wallet_balance');
            }
        });

        // 2. Modify wallet_transactions table to match target schema
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transactions', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('wallet_transactions', 'balance_after')) {
                $table->unsignedBigInteger('balance_after')->default(0)->after('amount');
            }
            if (!Schema::hasColumn('wallet_transactions', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('remarks');
                $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            }
        });

        // Populate transaction_id from transaction_code and balance_after from updated_balance for existing rows
        if (Schema::hasColumn('wallet_transactions', 'transaction_code')) {
            DB::statement("UPDATE wallet_transactions SET transaction_id = transaction_code WHERE transaction_id IS NULL");
        }
        if (Schema::hasColumn('wallet_transactions', 'updated_balance')) {
            DB::statement("UPDATE wallet_transactions SET balance_after = CAST(updated_balance AS UNSIGNED) WHERE balance_after = 0");
        }

        // Add unique index to transaction_id if not present
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('transaction_id')->unique()->change();
        });

        // 3. Update withdrawals table
        Schema::table('withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('withdrawals', 'request_id')) {
                $table->string('request_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('withdrawals', 'available_balance_at_request')) {
                $table->unsignedBigInteger('available_balance_at_request')->default(0)->after('user_id');
            }
            if (!Schema::hasColumn('withdrawals', 'amount_requested')) {
                $table->unsignedBigInteger('amount_requested')->default(0)->after('available_balance_at_request');
            }
            if (!Schema::hasColumn('withdrawals', 'rejection_remark')) {
                $table->text('rejection_remark')->nullable()->after('status');
            }
            if (!Schema::hasColumn('withdrawals', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('rejection_remark')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('withdrawals', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('processed_by');
            }
        });

        // Populate existing withdrawals if any
        if (Schema::hasColumn('withdrawals', 'amount')) {
            DB::statement("UPDATE withdrawals SET amount_requested = CAST(amount AS UNSIGNED) WHERE amount_requested = 0");
        }
        if (Schema::hasColumn('withdrawals', 'rejection_remarks')) {
            DB::statement("UPDATE withdrawals SET rejection_remark = rejection_remarks WHERE rejection_remark IS NULL");
        }
        if (Schema::hasColumn('withdrawals', 'approved_by')) {
            DB::statement("UPDATE withdrawals SET processed_by = approved_by WHERE processed_by IS NULL");
        }

        // 4. Create point_requests table
        if (!Schema::hasTable('point_requests')) {
            Schema::create('point_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_id')->unique();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedBigInteger('current_balance_at_request')->default(0);
                $table->unsignedBigInteger('points_requested');
                $table->text('remarks')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('rejection_remark')->nullable();
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_requests');

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn([
                'request_id',
                'available_balance_at_request',
                'amount_requested',
                'rejection_remark',
                'processed_by',
                'processed_at',
            ]);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_id',
                'balance_after',
                'reference_type',
                'reference_id',
            ]);
        });
    }
};
