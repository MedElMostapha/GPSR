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
            $table->string('file')->nullable(); // Chemin vers le fichier PDF du rapport
            $table->string('file_name')->nullable(); // URL vers le fichier PDF du rapport
            $table->enum('type', ['nationale', 'internationale']); // Type de mobilité (nationale ou internationale)
            $table->string('ville')->nullable(); // Ville (affiché si type 'nationale')
            $table->string('pays')->nullable(); // Pays (affiché si type 'internationale')
            $table->boolean('isValidated')->default(false); // Statut de validation, par défaut 'false'
            $table->date('date_debut'); // Date de debut de la mobilité
            $table->date('date_fin'); // Date de fin de la mobilité
            $table->date('date_validation')->nullable(); // Date de validation, si applicable
            $table->date('creation_date')->nullable(); // Date de validation, si applicable
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
