<?php

namespace Tests\Feature;

use App\Enums\RefreshJobStateEnum;
use App\Models\Oblast;
use App\Models\OblastsRefreshRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class OblastControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_oblasts_near_coordinates()
    {
        $oblast1 = Oblast::factory()
            ->withCoordinates(50.4501, 30.5234)
            ->withPolygon()
            ->create(['name' => 'kyiv']);

        $oblast2 = Oblast::factory()
            ->withCoordinates(50.4502, 30.5235)
            ->withPolygon()
            ->create(['name' => 'kharkiv']);

        $response = $this->getJson(route('api.oblasts.index', [
            'lat' => 50.4500,
            'lon' => 30.5230
        ]));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'kyiv')
            ->assertJsonPath('data.1.name', 'kharkiv');
    }

    public function test_requires_lat_and_lon_parameters()
    {
        $this->getJson(route('api.oblasts.index'))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['lat', 'lon']);

        $this->getJson(route('api.oblasts.index', ['lat' => 50.4500]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['lon']);

        $this->getJson(route('api.oblasts.index', ['lon' => 30.5230]))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['lat']);
    }

    public function test_can_create_refresh_job()
    {
        Queue::fake();
        $response = $this->postJson(route('api.oblasts.createRefreshJob'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => ['id']
            ]);


        $this->assertDatabaseHas('oblasts_refresh_records', [
            'id' => $response->json()['data']['id'],
            'state' => RefreshJobStateEnum::PENDING->value,
        ]);
    }

    public function test_returns_conflict_when_refresh_job_already_processing()
    {
        Queue::fake();
        OblastsRefreshRecord::factory()->create(['state' => RefreshJobStateEnum::PROCESSING->value]);

        $response = $this->postJson(route('api.oblasts.createRefreshJob'));

        $response->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson(['message' => 'The refresh processing is already running.']);
    }

    public function test_can_get_refresh_job_status()
    {
        $job = OblastsRefreshRecord::factory()->create([
            'state' => RefreshJobStateEnum::PROCESSING->value,
        ]);

        $response = $this->getJson(route('api.oblasts.getRefreshJobStatus', $job));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.state', 'processing');
    }

    public function test_returns_not_found_for_invalid_job_id()
    {
        $response = $this->getJson(route('api.oblasts.getRefreshJobStatus', 999));

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_can_purge_all_oblasts_data()
    {
        Oblast::factory()->count(3)->create();

        $response = $this->deleteJson(route('api.oblasts.destroy'));

        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseCount('oblasts', 0);
    }

    public function test_can_create_refresh_job_with_delay()
    {
        Queue::fake();
        $response = $this->postJson(route('api.oblasts.createRefreshJob'), [
            'delay' => 60
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => ['id']
            ]);

        $this->assertDatabaseHas('oblasts_refresh_records', [
            'id' => $response->json()['data']['id'],
            'state' => RefreshJobStateEnum::PENDING->value,
            'delay_ts' => now()->addSeconds(60),
        ]);
    }
}
