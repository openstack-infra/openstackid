<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

        $this->call('OpenIdExtensionsSeeder');

        $this->command->info('ServerExtension table seeded!');
	}

}