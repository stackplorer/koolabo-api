<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

use Tests\TestCase;
use App\Models\User;
// use App\Models\Skill;
use App\Models\Job;
use App\Models\City;
use App\Models\Listing;

use Exception; // for testing purposes

class ListingTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_users_can_create_listing()
    {
        $this->seed();

        $data = [
            'title' => "Some Title Lorem Ipsum",
            'description' => "Hey guys! I am a Berlin-based DJ who will {$this->faker->paragraph()}",
            'city_id' => City::inRandomOrder()->first()->id,
            'job_id' => City::inRandomOrder()->first()->id
        ];

        $user = User::factory()->create();
        
        $response = $this->actingAs($user, 'api')
                         ->json('POST', '/api/listings', $data);

        $response->assertStatus(201);

        // $response->assertJson(['status' => true]);
        // $response->assertJson(['message' => "Listing Created!"]);


        // throw new Exception(dd($response));
        // throw new Exception(dd($response->getAttributes()));

        // $response->assertJson(['data' => $data]);

        // $responseData = $response->getContent();

        foreach ($data as $key => $value)
        {
            $this->assertEquals($responseData[$key], $value);
        }

        $this->assertEquals(3+2, 89);

        // assert that listing was entered into database
        $this->assertNotNull(Listing::where('title', "Some Title Lorem Ipsum")->get());
    }

    public function test_users_can_not_create_listing_without_authentication()
    {
        $this->seed();

        $data = [
            'title' => "Looking for an experienced Filmmaker who can make a music video for my upcoming techno single",
            'description' => $this->faker->paragraph(),
            'city_id' => City::inRandomOrder()->first()->id,
            'job_id'=> Job::inRandomOrder()->first()->id
        ];

        $response = $this->json('POST', '/api/listings', $data);
        $response->assertStatus(401);
        // $response->assertJson(['message' => "Unauthenticated"]);
        // assert that it was not entered into database
    }

    public function test_users_can_read_listing()
    {
        $this->seed();

        $listing = Listing::inRandomOrder()->first();
        $response = $this->json('GET', '/api/listings/'.$listing->id);
        $response->assertStatus(200);
    }

    public function test_users_can_read_all_listings()
    {
        $this->seed();

        $response = $this->json('GET', '/api/listings');
        $response->assertStatus(200);
        $response->assertJsonStructure(
            [
                [
                    'id',
                    'title',
                    'description',
                    'slug',
                    'is_active',
                    'posted_by',
                    'city_id',
                    'job_id',
                    'ends_at',
                    'created_at',
                    'updated_at',
                ]
            ]
        );
    }

    public function test_users_can_update_listing()
    {
        $this->seed();

        $user = User::factory()->create();

        $listing = Listing::factory()->create([
            'posted_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')->json(
                'PATCH',
                '/api/listings/'.$listing->id,
                [
                    'title' => "New Title"
                ]
        );

        $response->assertStatus(200);
        $response->assertJson(['title' => "New Title"]);
    }

    public function test_users_can_not_update_listing_of_others()
    {
        $this->seed();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $listing = Listing::factory()->create([
            'posted_by' => $userB->id,
        ]);

        $response = $this->actingAs($userA, 'api')->json(
                'PATCH',
                '/api/listings/'.$listing->id,
                [
                    'title' => "New Title"
                ]
        );

        $this->assertTrue(false, "Pending: what assertions should be made here?");
    }

    public function test_users_can_delete_listing()
    {
        $this->seed();

        $user = User::factory()->create();

        $listing = Listing::factory()->create([
            'posted_by' => $user->id,
        ]);

        $response = $this->actingAs($user, 'api')->json(
            'DELETE',
            '/api/listings/'.$listing->id,
        );

        $response->assertStatus(200);

        $this->assertTrue(Listing::find($listing->id) == null);
    }

    public function test_users_can_not_delete_listing_of_others()
    {
        $this->seed();
        
        $user = User::factory()->create();

        $listing = Listing::inRandomOrder()->first();

        $response = $this->actingAs($user, 'api')->json(
            'DELETE',
            'api/listings/'.$listing->id,
        );

        $response->assertStatus(403);
        $response->assertJson(['message' => "Unauthorized action."]);

        // assert that the listing is still in the db
        $this->assertNotNull(Listing::find($listing->id));
    }
}