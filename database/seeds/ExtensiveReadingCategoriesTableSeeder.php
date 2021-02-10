<?php

use App\ExtensiveReadingCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ExtensiveReadingCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ExtensiveReadingCategory::class, 5)->create();
        ExtensiveReadingCategory::create(['name' => 'Human-Computer Interaction', 'description' => 'Covers human factors, user interfaces, and collaborative computing.']);
        ExtensiveReadingCategory::create(['name' => 'Computation and Language', 'description' => 'Covers natural language processing. Roughly includes material']);
        ExtensiveReadingCategory::create(['name' => 'Data Structures and Algorithms', 'description' => 'Covers data structures and analysis of algorithms. Roughly includes material']);
        ExtensiveReadingCategory::create(['name' => 'Computational Complexity', 'description' => 'Covers models of computation, complexity classes, structural complexity, complexity tradeoffs, upper and lower bounds.']);
        ExtensiveReadingCategory::create(['name' => 'Cryptography and Security', 'description' => 'Covers all areas of cryptography and security including authentication, public key cryptosytems, proof-carrying code, etc.']);
    }
}
