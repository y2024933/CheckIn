<?php

use Illuminate\Database\Seeder;

class MakeRecord extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        for ($i = 0; $i < 50; $i++) {
            App\Models\account_record::create([
                'accountid' => $faker->numberBetween($min = 1, $max = 5),
                'type'      => $faker->numberBetween($min = 1, $max = 2),
                'status'    => '1',
                'date'      => $faker->dateTimeBetween($startDate = '2020-01-01 00:00:00', $endDate = 'now', $timezone = null),
            ]);
        }
    }
}
