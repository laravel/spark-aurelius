<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSparkSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('stripe_id');
            $table->string('stripe_plan')->nullable();
            $table->string('stripe_status')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('team_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('team_id');
            $table->string('name');
            $table->string('stripe_id');
            $table->string('stripe_plan')->nullable();
            $table->string('stripe_status')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('stripe_id')->index();
            $table->string('stripe_plan');
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['subscription_id', 'stripe_plan']);
        });

        Schema::create('team_subscription_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('subscription_id');
            $table->string('stripe_id')->index();
            $table->string('stripe_plan');
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['subscription_id', 'stripe_plan']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('subscriptions');
        Schema::drop('team_subscriptions');
        Schema::drop('subscription_items');
        Schema::drop('team_subscription_items');
    }
}
