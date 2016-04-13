<?php

use Illuminate\Database\Migrations\Migration;

class AddApiScopesGroups extends Migration
{

    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('oauth2_api_scope_group', function ($table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('name', 512);
            $table->text('description');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('oauth2_api_scope_group_scope', function($table)
        {
            $table->timestamps();

            $table->bigInteger("group_id")->unsigned();
            $table->index('group_id');
            $table->foreign('group_id')
                ->references('id')
                ->on('oauth2_api_scope_group')
                ->onDelete('cascade')
                ->onUpdate('no action'); ;

            $table->bigInteger("scope_id")->unsigned();
            $table->index('scope_id');
            $table->foreign('scope_id')
                ->references('id')
                ->on('oauth2_api_scope')
                ->onDelete('cascade')
                ->onUpdate('no action');
        });

        Schema::create('oauth2_api_scope_group_users', function($table)
        {
            $table->timestamps();

            $table->bigInteger("group_id")->unsigned();
            $table->index('group_id');
            $table->foreign('group_id')
                ->references('id')
                ->on('oauth2_api_scope_group')
                ->onDelete('cascade')
                ->onUpdate('no action'); ;

            $table->bigInteger("user_id")->unsigned();
            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('openid_users')
                ->onDelete('cascade')
                ->onUpdate('no action');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth2_api_scope_group');
        Schema::dropIfExists('oauth2_api_scope_group_scope');
        Schema::dropIfExists('oauth2_api_scope_group_users');
    }
}
