<?php

namespace App\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Psr\Container\ContainerInterface;

class AppMigration extends Migration
{
    protected $ioc;

    public function __construct(ContainerInterface $ioc)
    {
        $this->ioc = $ioc;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = $this->ioc['db']->connection()->getSchemaBuilder();

        $schema->create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('email');
            $table->text('task_text');
            $table->string('task_status')->nullable()->default('incomplete');
            $table->string('updated_by')->nullable();
            $table->nullableTimestamps();

            $table->engine = 'InnoDB';
            $table->unique('task_text');
        });

        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('email')->nullable();
            $table->string('password');
            $table->nullableTimestamps();

            $table->engine = 'InnoDB';
            $table->unique('email');
        });

        //
        $this->ioc['db']->table('users')->insert([
            'username' => 'admin',
            'password' => password_hash('123', PASSWORD_BCRYPT),
        ]);

        return;

        // $schema->create('user_tasks', function (Blueprint $table) {
        //     $table->increments('id');
        //     $table->integer('user_id')->unsigned();
        //     $table->integer('task_id')->unsigned();
        //     $table->nullableTimestamps();

        //     $table->engine = 'InnoDB';
        //     $table->unique(['user_id', 'task_id']);
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $schema = $this->ioc['db']->connection()->getSchemaBuilder();

        $tables = ['users', 'tasks'];

        foreach ($tables as $table) {
            if ($schema->hasTable($table)) {
                $schema->drop($table);
            }
        }
    }
}
