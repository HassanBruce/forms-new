<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tenant: one agency (parent company) per row.
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('currency', 3)->default('GBP');
            $table->string('booking_prefix', 6)->default('TD');
            $table->unsignedInteger('next_booking_no')->default(10001);
            $table->unsignedInteger('next_invoice_no')->default(1);
            $table->string('plan', 20)->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });

        // Sub-brands (Director level). Each has its own invoice branding.
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 10);
            $table->string('color', 7)->default('#16A34A');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('registration')->nullable(); // ATOL / company no.
            $table->text('bank_details')->nullable();
            $table->text('invoice_terms')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('lead_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 20); // flight, hotel, transport, visa, other
            $table->string('contact')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('team_id')->nullable()->index();
            $table->foreignId('agent_id')->index();
            $table->string('ref', 20);
            $table->string('profile_no')->nullable();
            $table->string('lead_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('pnr', 20)->nullable();
            $table->string('airline')->nullable();
            $table->string('flight_supplier')->nullable();
            $table->text('flight_details')->nullable();
            $table->date('travel_start')->nullable();
            $table->date('travel_end')->nullable();
            $table->string('status', 20)->default('draft'); // draft, waiting, accepted, rejected
            $table->unsignedTinyInteger('step')->default(1);
            $table->decimal('total_sale', 12, 2)->default(0);
            $table->decimal('total_purchase', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->string('currency', 3)->default('GBP');
            $table->string('invoice_no', 30)->nullable();
            $table->timestamp('invoice_sent_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'ref']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('booking_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('salutation', 10)->nullable();
            $table->string('first_name');
            $table->string('surname');
            $table->string('type', 10)->default('adult'); // adult, child, infant
            $table->unsignedSmallInteger('position')->default(0);
        });

        Schema::create('booking_hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('city')->nullable();
            $table->string('hotel_name')->nullable();
            $table->string('room_type')->nullable();
            $table->unsignedSmallInteger('rooms')->default(1);
            $table->string('meal_type')->nullable();
            $table->string('view_type')->nullable();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->string('supplier')->nullable();
            $table->string('ref_no')->nullable();
            $table->string('hcn')->nullable();
        });

        Schema::create('booking_transports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_type')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->string('route')->nullable();
            $table->string('supplier')->nullable();
        });

        Schema::create('booking_visas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('visa_type')->nullable();
            $table->string('supplier')->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
        });

        // Customer-facing selling price per pax type (page 3).
        Schema::create('booking_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('pax_type', 30);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
        });

        // Internal purchase costs (page 4, profit / loss).
        Schema::create('booking_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('category', 20); // flight, hotel, transport, visa, other
            $table->string('description');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_cost', 12, 2)->default(0);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->date('paid_on');
            $table->string('reference')->nullable();
            $table->string('method', 30);
            $table->decimal('amount', 12, 2);
            $table->string('proof_path')->nullable();
            $table->foreignId('recorded_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['payments', 'booking_costs', 'booking_prices', 'booking_visas', 'booking_transports', 'booking_hotels', 'booking_passengers', 'bookings', 'suppliers', 'teams', 'brands', 'companies'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
