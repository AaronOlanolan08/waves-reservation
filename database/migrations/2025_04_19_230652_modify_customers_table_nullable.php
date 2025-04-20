<?php
// filepath: c:\Users\Tenchavez\StudioProjects\winds-reservation\database\migrations\xxxx_xx_xx_xxxxxx_modify_customers_table_nullable.php
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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->change();
            $table->string('password', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email', 255)->nullable(false)->change();
            $table->string('password', 50)->nullable(false)->change();
        });
    }
};