<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * conversion_logs held one row per raw meat used, and repeated the conversion's own facts (the
 * dish, servings produced, servings before/after, who converted it, remarks) on every meat's row,
 * tied together by batch_code. Split into a header and its lines, like orders / order_details:
 * - conversions: one row per conversion
 * - conversion_lines: one row per raw meat used in it (its date is the conversion's)
 *
 * Existing logs are copied in one transaction, and only while conversions is still empty, so
 * running the migration again never duplicates them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('conversions')) {
            Schema::create('conversions', function (Blueprint $table) {
                $table->id();
                // Older conversions were made per raw meat, before they were linked to a dish
                $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
                $table->decimal('servings_produced', 10, 4);
                $table->decimal('previous_servings', 10, 4)->default(0);
                $table->decimal('new_servings', 10, 4)->default(0);
                $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('conversion_lines')) {
            Schema::create('conversion_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversion_id')->constrained('conversions')->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->decimal('raw_quantity_used', 10, 4);
                $table->string('unit', 50);
                $table->decimal('portion_size', 10, 4);
                $table->decimal('previous_raw_stock', 10, 4)->default(0);
                $table->decimal('remaining_raw_stock', 10, 4)->default(0);
            });
        }

        if (Schema::hasTable('conversion_logs') && DB::table('conversions')->doesntExist()) {
            DB::transaction(function () {
                $batches = DB::table('conversion_logs')->orderBy('id')->get()
                    ->groupBy(fn ($log) => $log->batch_code ?? 'log-' . $log->id);

                foreach ($batches as $logs) {
                    $first = $logs->first();
                    $conversionId = DB::table('conversions')->insertGetId([
                        'menu_item_id'      => $first->menu_item_id,
                        'servings_produced' => $first->rtc_units_produced,
                        'previous_servings' => $first->previous_rtc_servings,
                        'new_servings'      => $first->new_rtc_servings,
                        'converted_by'      => $first->converted_by,
                        'remarks'           => $first->remarks,
                        'created_at'        => $first->created_at,
                        'updated_at'        => $first->updated_at,
                    ]);

                    foreach ($logs as $log) {
                        DB::table('conversion_lines')->insert([
                            'conversion_id'       => $conversionId,
                            'inventory_item_id'   => $log->inventory_item_id,
                            'raw_quantity_used'   => $log->raw_quantity_used,
                            'unit'                => $log->unit,
                            'portion_size'        => $log->portion_size,
                            'previous_raw_stock'  => $log->previous_raw_stock,
                            'remaining_raw_stock' => $log->remaining_raw_stock,
                        ]);
                    }
                }
            });
        }

        Schema::dropIfExists('conversion_logs');
    }

    public function down(): void
    {
        if (! Schema::hasTable('conversion_logs')) {
            Schema::create('conversion_logs', function (Blueprint $table) {
                $table->id();
                $table->string('batch_code', 36)->nullable()->index();
                $table->timestamps();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->foreignId('menu_item_id')->nullable()->constrained('menu_items')->nullOnDelete();
                $table->decimal('raw_quantity_used', 10, 4);
                $table->string('unit', 50);
                $table->decimal('portion_size', 10, 4);
                $table->decimal('rtc_units_produced', 10, 4);
                $table->decimal('previous_raw_stock', 10, 4)->default(0);
                $table->decimal('remaining_raw_stock', 10, 4)->default(0);
                $table->decimal('previous_rtc_servings', 10, 4)->default(0);
                $table->decimal('new_rtc_servings', 10, 4)->default(0);
                $table->foreignId('converted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('remarks')->nullable();
            });
        }

        if (Schema::hasTable('conversions') && DB::table('conversion_logs')->doesntExist()) {
            DB::transaction(function () {
                foreach (DB::table('conversions')->orderBy('id')->get() as $c) {
                    $lines = DB::table('conversion_lines')->where('conversion_id', $c->id)->orderBy('id')->get();
                    // A conversion with several meats shares a batch code again (one row per meat)
                    $batchCode = $lines->count() > 1 ? sprintf('conversion-%d', $c->id) : null;

                    foreach ($lines as $line) {
                        DB::table('conversion_logs')->insert([
                            'batch_code'            => $batchCode,
                            'created_at'            => $c->created_at,
                            'updated_at'            => $c->updated_at,
                            'inventory_item_id'     => $line->inventory_item_id,
                            'menu_item_id'          => $c->menu_item_id,
                            'raw_quantity_used'     => $line->raw_quantity_used,
                            'unit'                  => $line->unit,
                            'portion_size'          => $line->portion_size,
                            'rtc_units_produced'    => $c->servings_produced,
                            'previous_raw_stock'    => $line->previous_raw_stock,
                            'remaining_raw_stock'   => $line->remaining_raw_stock,
                            'previous_rtc_servings' => $c->previous_servings,
                            'new_rtc_servings'      => $c->new_servings,
                            'converted_by'          => $c->converted_by,
                            'remarks'               => $c->remarks,
                        ]);
                    }
                }
            });
        }

        Schema::dropIfExists('conversion_lines');
        Schema::dropIfExists('conversions');
    }
};
