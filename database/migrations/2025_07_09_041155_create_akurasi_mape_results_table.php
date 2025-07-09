<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAkurasiMapeResultsTable extends Migration
{
    public function up()
    {
        Schema::create('akurasi_mape_results', function (Blueprint $table) {
            $table->id();
            $table->decimal('monte_akurasi_datang', 5, 2)->nullable();
            $table->decimal('monte_mape_datang', 5, 2)->nullable();
            $table->decimal('monte_akurasi_berangkat', 5, 2)->nullable();
            $table->decimal('monte_mape_berangkat', 5, 2)->nullable();
            $table->decimal('tes_akurasi_datang', 5, 2)->nullable();
            $table->decimal('tes_mape_datang', 5, 2)->nullable();
            $table->decimal('tes_akurasi_berangkat', 5, 2)->nullable();
            $table->decimal('tes_mape_berangkat', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('akurasi_mape_results');
    }
}