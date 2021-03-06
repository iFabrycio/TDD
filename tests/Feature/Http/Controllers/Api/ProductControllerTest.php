<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\TestCase;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase; //Remove the data in  db after the tests, so, don't populate de db

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Basically have two ways to declare that a function is a test for TDD 
     *  The first way is 'test' prefix in every function on test class
     *  The second way is just an '@ with a test' here.
     * @test
     */
    public function non_authenticated_users_cannot_access_the_following_endpoints_for_the_product_api()
    {
        $index = $this->json('GET', 'api/products');
        $index->assertStatus(401);

        $store = $this->json('POST', 'api/products');
        $store->assertStatus(401);

        $update = $this->json('PUT', 'api/products/-1');
        $update->assertStatus(401);

        $destroy = $this->json('DELETE', 'api/products/-1');
        $destroy->assertStatus(401);

        $show = $this->json('GET', 'api/products/-1');
        $show->assertStatus(401);
    }

    /**
     * @test
     */
    public function can_return_a_collection_of_paginated_products()
    {
        $product1 = $this->create('Product');
        $product2 = $this->create('Product');
        $product3 = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('GET', '/api/products');

        $response->assertStatus(200)
        ->assertJsonStructure([
            'data'=>[
                '*'=>[ 'id', 'name', 'slug', 'price', 'created_at',]
            ],
            'links'=>[
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'last_page', 'from', 'to', 'path',
                'per_page', 'total',
            ],
        ]);

    }

    /**
      * @test
      */
    public function can_create_a_product()
    {
        //Given
            //user is authenticated(prerequesite)
        //When
            //post request create product(function)
        $faker = Factory::create();

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('POST', '/api/products', [
                'name' => $name = $faker->company,
                'slug' => str_slug($name),
                'price' => $price = random_int(10, 100),
            ]); //This line send a requisition
            \Log::info(1, [$response->getContent()]);
        //Then
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

    /**
     * @test
     */
    public function will_fail_with_a_404_if_product_is_not_found()
    {
        $response = $this->actingAs($this->create('User', [], false), 'api')->json('GET', 'api/products/-1');

        $response->assertStatus(404);
    }
    /**
     * @test
     */
    
    public function can_return_a_product()
    {
        //Given
        // dd($product);
        $product = $this->create('Product');//Accessing the function on TestCase
        //When
        $response = $this->actingAs($this->create('User', [], false), 'api')->json('GET', "api/products/$product->id");
        
        //Then
        $response->assertStatus(200) //Verifica se o status é 200
            ->assertExactJson([ //Verifica se o Json Enviado é igual ao modelo dado abaixo
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->price,
                'created_at' => (string)$product->created_at,
                'updated_at' => (string)$product->updated_at,

            ]);

    }

    /**
     * @test
     */
    public function will_fail_with_a_404_if_product_we_want_to_update_is_not_found()
    {
        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', 'api/products/-1');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function can_update_a_product()
    {
        $product = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('PUT', "api/products/$product->id", [
            'name' => $product->name.'_updated',
            'slug' => str_slug($product->name.'_updated'),
            'price' => $product->price + 10,
        ]);

            $response->assertStatus(200)
                ->assertExactJson([
                    'id' => $product->id,
                    'name' => $product->name.'_updated',
                    'slug' => str_slug($product->name.'_updated'),
                    'price' => (int)$product->price + 10,
                    'created_at' => (string)$product->created_at,
                    'updated_at' => (string)$product->updated_at
                ]);

            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'name' => $product->name.'_updated',
                'slug' => str_slug($product->name.'_updated'),
                'price' => (int)$product->price + 10,
                'created_at' => (string)$product->created_at,
                'updated_at' => (string)$product->updated_at
            ]);
    }
    /**
     * @test
     */
    public function will_fail_with_a_404_if_product_we_want_to_delete_is_not_found()
    {
        // $product = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('DELETE', "/api/products/-1");

        $response->assertStatus(404);
    }
    /**
     * @test
     */
    public function can_delete_a_product()
    {
        $product = $this->create('Product');

        $response = $this->actingAs($this->create('User', [], false), 'api')->json('DELETE', "/api/products/$product->id");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }


}
