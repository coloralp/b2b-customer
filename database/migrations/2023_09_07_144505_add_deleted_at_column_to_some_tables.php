<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('categories', function (Blueprint $table) {

            if (!Schema::hasColumn('categories', 'deleted_at')) {

                $table->softDeletes();
            }
        });

        Schema::table('customers', function (Blueprint $table) {

            if (!Schema::hasColumn('customers', 'deleted_at')) {

                $table->softDeletes();
            }

        });

        Schema::table('exchnage_infos', function (Blueprint $table) {

            if (!Schema::hasColumn('exchnage_infos', 'deleted_at')) {

                $table->softDeletes();
            }
        });
        Schema::table('games', function (Blueprint $table) {

            if (!Schema::hasColumn('games', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('keys', function (Blueprint $table) {

            if (!Schema::hasColumn('keys', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('languages', function (Blueprint $table) {

            if (!Schema::hasColumn('languages', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('market_places', function (Blueprint $table) {

            if (!Schema::hasColumn('market_places', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('marketplace_match_games', function (Blueprint $table) {

            if (!Schema::hasColumn('marketplace_match_games', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('moneyboxes', function (Blueprint $table) {

            if (!Schema::hasColumn('moneyboxes', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('offers', function (Blueprint $table) {

            if (!Schema::hasColumn('offers', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('orders', function (Blueprint $table) {

            if (!Schema::hasColumn('orders', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'deleted_at')) {
                $table->softDeletes();
            }

        });
        Schema::table('publishers', function (Blueprint $table) {

            if (!Schema::hasColumn('publishers', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('regions', function (Blueprint $table) {

            if (!Schema::hasColumn('regions', 'deleted_at')) {

                $table->softDeletes();
            }

        });

        Schema::table('role_options', function (Blueprint $table) {

            if (!Schema::hasColumn('role_options', 'deleted_at')) {

                $table->softDeletes();
            }

        });

        Schema::table('sale_infos', function (Blueprint $table) {

            if (!Schema::hasColumn('sale_infos', 'deleted_at')) {
                $table->softDeletes();
            }

        });
        Schema::table('suppliers', function (Blueprint $table) {

            if (!Schema::hasColumn('suppliers', 'deleted_at')) {

                $table->softDeletes();
            }

        });
        Schema::table('users', function (Blueprint $table) {

            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {

            if (Schema::hasColumn('categories', 'deleted_at')) {

                $table->dropSoftDeletes();
            }
        });

        Schema::table('customers', function (Blueprint $table) {

            if (Schema::hasColumn('customers', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });

        Schema::table('exchnage_infos', function (Blueprint $table) {

            if (Schema::hasColumn('exchnage_infos', 'deleted_at')) {

                $table->dropSoftDeletes();
            }
        });
        Schema::table('games', function (Blueprint $table) {

            if (Schema::hasColumn('games', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('keys', function (Blueprint $table) {

            if (Schema::hasColumn('keys', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('languages', function (Blueprint $table) {

            if (Schema::hasColumn('languages', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('market_places', function (Blueprint $table) {

            if (Schema::hasColumn('market_places', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('marketplace_match_games', function (Blueprint $table) {

            if (Schema::hasColumn('marketplace_match_games', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('moneyboxes', function (Blueprint $table) {

            if (Schema::hasColumn('moneyboxes', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('offers', function (Blueprint $table) {

            if (Schema::hasColumn('offers', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('orders', function (Blueprint $table) {

            if (Schema::hasColumn('orders', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

        });
        Schema::table('publishers', function (Blueprint $table) {

            if (Schema::hasColumn('publishers', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('regions', function (Blueprint $table) {

            if (Schema::hasColumn('regions', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });

        Schema::table('role_options', function (Blueprint $table) {

            if (Schema::hasColumn('role_options', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });

        Schema::table('sale_infos', function (Blueprint $table) {

            if (Schema::hasColumn('sale_infos', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

        });
        Schema::table('suppliers', function (Blueprint $table) {

            if (Schema::hasColumn('suppliers', 'deleted_at')) {

                $table->dropSoftDeletes();
            }

        });
        Schema::table('users', function (Blueprint $table) {

            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

        });

    }
};
