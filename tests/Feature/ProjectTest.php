<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Topic;

class ProjectTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /*
     *   POST & PATCH requests to /api/projects should not include
     *   `created_by` and `manager_id` fields since these fields
     *   are set by the Controller.
     */
    private function generate_dummy_form_data()
    {
        $data = Project::factory()->make()->toArray();
        unset($data['created_by']);
        unset($data['manager_id']);

        return $data;
    }

    public function test_eloquent_relationships()
    {
        $this->seed();

        $nLikes = 20;
        $nFollowers = 5;
        $nTopics = 3;
        $nMembers = 4; // team size


        $user = User::inRandomOrder()->first();
        $project = Project::factory()->for($user, 'manager')->create();
        $project->images()->create(['path' => 'image1.png']);
        $project->images()->create(['path' => 'image2.png']);
        $project->likes()->attach(User::inRandomOrder()->take($nLikes)->get());
        $project->followers()->attach(User::inRandomOrder()->take($nFollowers)->get());
        $project->topics()->attach(Topic::inRandomOrder()->take($nTopics)->get());
        $project->members()->attach(User::inRandomOrder()->take($nMembers)->get());

        $project->refresh();

        $this->assertEquals(2, $project->images->count());
        $this->assertEquals($user->id, $project->manager->id);
        $this->assertEquals($nLikes, $project->likes->count());
        $this->assertEquals($nFollowers, $project->followers->count());
        $this->assertEquals($nTopics, $project->topics->count());
        $this->assertEquals($nMembers, $project->members->count());
        

    }
    
    public function test_unauthenticated_user_cannot_create_project()
    {
        $this->seed();

        $data = $this->generate_dummy_form_data();

        $response = $this->json('POST', '/api/projects', $data);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_project()
    {
        $this->seed();

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $data = $this->generate_dummy_form_data();

        $response = $this->json('POST', '/api/projects', $data);
        
        $response->assertStatus(201);
    }

    public function test_unauthorized_user_cannot_delete_project()
    {
        $this->seed();
        
        $project = Project::inRandomOrder()->first();
        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $response = $this->json('DELETE', '/api/projects/'.$project->id);
        $response->assertStatus(403);
    }

    public function test_project_manager_can_delete_project()
    {
        $this->seed();

        $project = Project::inRandomOrder()->first();

        Sanctum::actingAs($project->manager, ['*']);

        $response = $this->json('DELETE', '/api/projects/'.$project->id);
        $response->assertStatus(200);
    }

    public function test_project_manager_can_edit_project()
    {
        $this->seed();

        $project = Project::inRandomOrder()->first();

        Sanctum::actingAs($project->manager, ['*']);

        $data = $this->generate_dummy_form_data();

        $response = $this->json('PATCH', '/api/projects/'.$project->id, $data);

        $response->assertStatus(200); // should it be 204 instead?
    }

    /*
     *   Only project manager should be able to edit project
     */
    public function test_unauthorized_user_cannot_edit_project()
    {
        $this->seed();

        $user = User::factory()->create();
        $project = Project::inRandomOrder()->first();

        Sanctum::actingAs($user, ['*']);

        $data = ['title' => 'Here is a New Title'];

        $response = $this->json('PATCH', '/api/projects/'.$project->id, $data);

        $response->assertStatus(403);
    }

    public function test_project_creator_is_appointed_as_project_manager_by_default()
    {
        $this->seed();

        $user = User::factory()->create();

        Sanctum::actingAs($user, ['*']);

        $data = $this->generate_dummy_form_data();

        $response = $this->json('POST', '/api/projects', $data);

        $response->assertStatus(201);

        $managerId = (int) json_decode($response->content())->manager_id;

        $this->assertEquals($managerId, $user->id);
    }

    public function test_project_creator_can_appoint_someone_else_as_project_manager()
    {
        $this->seed();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Sanctum::actingAs($userA, ['*']);

        $data = $this->generate_dummy_form_data();

        // user A creates a new project
        $response = $this->json('POST', '/api/projects', $data);
        $response->assertStatus(201);
        $projectId = (int) json_decode($response->content())->id;

        // user A updates project so that user B is now the manager
        $response = $this->json('PATCH', '/api/projects/'.$projectId, ['manager_id' => $userB->id]);
        $response->assertStatus(200); // should it be 204 instead?

        $response = $this->json('GET', '/api/projects/'.$projectId);
        $response->assertStatus(200);
        $managerId = (int) json_decode($response->content())->manager_id;

        $this->assertEquals($managerId, $userB->id);
    }

    /*
     *   An invitation to simply join the project (no job assigned yet).
     *//*
    public function test_project_manager_can_invite_user_to_join_project()
    {
        $this->assertTrue(False);
    }

    public function test_project_manager_can_cancel_invitation()
    {
        $this->assertTrue(False);
    }

    public function test_user_can_leave_project_unless_is_project_manager()
    {
        $this->assertTrue(False);
    }

    public function test_user_can_request_to_join_project()
    {
        $this->assertTrue(False);
    }

    public function test_user_can_cancel_request()
    {
        $this->assertTrue(False);
    }

    public function test_project_manager_can_decline_user_request_to_join_project()
    {
        $this->assertTrue(False);
    }

    public function test_project_manager_can_accept_user_request_to_join_project()
    {
        $this->assertTrue(False);        
    }
    */
}