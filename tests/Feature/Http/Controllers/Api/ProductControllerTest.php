<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    /**
     * Basically have two ways to declare that a function is a test for TDD 
     *  The first way is 'test' prefix in every function on test class
     *  The second way is just an '@ with a test' here.
     * @test
     */
    public function can_create_a_product()
    {
        //Given
            //user is authenticated(prerequesite)
        //When
            //post request create product(function)
        $faker = Factory::create();

        $response = $this->json('POST', '/api/products', [
                'name' => $name = $faker->company,
                'slug' => str_slug($name),
                'price' => $price = random_int(10, 100),
            ]);
            \Log::info(1, [$response->getContent()])
;        //Then
            //Product exists(result)
        $response->assertJsonStructure([
            'id', 'name', 'slug', 'price', 'created_at'
        ]) //Attributes that we expect in result array
        ->assertJson([
            'name'=> $name,
            'slug' =>  str_slug($name),
            'price' => $price
        ])
        ->assertStatus(201);

        $this->assertDataBaseHas('products', [
            'name'=> $name,
            'slug' => str_slug($name),
            'price' => $price,
        ]);
        
    }
}
