<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMobilitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobilites', function (Blueprint $table) {
            $table->id(); // Identifiant unique
            $table->string('labo_accueil'); // Labo d'accueil
            $table->string('rapport_mobilite'); // Chemin vers le fichier PDF du rapport
            $table->enum('type', ['nationale', 'internationale']); // Type de mobilité (nationale ou internationale)
            $table->string('ville')->nullable(); // Ville (affiché si type 'nationale')
            $table->string('pays')->nullable(); // Pays (affiché si type 'internationale')
            $table->boolean('isValidated')->default(false); // Statut de validation, par défaut 'false'
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->timestamps(); // Création des timestamps (created_at, updated_at)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobilites');
    }
}
