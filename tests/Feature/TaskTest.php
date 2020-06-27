<?php

namespace Tests\Feature;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use App\Task;

class TaskTest extends TestCase
{
    // PHPUnit 8 deprecates assertArraySubset method,
    // so we'll use an external package.
    use ArraySubsetAsserts;
    
    use RefreshDatabase;
    use WithFaker;

    private const TASK_JSON_STRUCTURE = [
        'id', 'title', 'description', 'status', 'user_id', 'created_at', 'updated_at'
    ];

    private const LIST_RESPONSE_STRUCTURE = ['*' => self::TASK_JSON_STRUCTURE];

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class, 10)->create()[0];
        factory(Task::class, 25)->create();
    }

    public function testReturnsListFilteredByStatusAndSortedByUserCreationTime()
    {
        // user-sort=desc to sort from new users to old ones.
        $response = $this->get('/api/tasks?status=in-progress&user-sort=desc');

        $response->assertOk()->assertJsonStructure(self::LIST_RESPONSE_STRUCTURE);
        $list = $response->json();

        $this->assertEquals('In Progress', $list[0]['status']);
        for ($i = 1, $c = count($list); $i < $c; ++$i) {
            $this->assertEquals('In Progress', $list[$i]['status']);
            // An old user has less (<) id number value than the previous new user.
            // Or this user is the same (=) as the previous one.
            $this->assertTrue($list[$i]['user_id'] <= $list[$i - 1]['user_id']);
        }
    }

    public function testCreatesTaskAndAssignItTheUserThatIsCreatingIt()
    {
        $data = [
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->sentence
        ];

        $response = $this->post('/api/tasks', $data, $this->getAuthorizationHeaders())
            ->assertCreated()
            ->assertJsonStructure(self::TASK_JSON_STRUCTURE)
            ->assertJsonFragment($data);
        
        $this->assertNotNull($createdTask = Task::query()->find($response['id']));
        self::assertArraySubset(
            array_merge($data, ['user_id' => $this->user->getKey()]),
            $createdTask->getAttributes()
        );
    }

    public function testEditsTask()
    {
        $data = ['title' => 'Edited', 'description' => 'Task'];

        $this->post("/api/tasks/edit/1", $data, $this->getAuthorizationHeaders())
            ->assertOk()->assertJsonStructure(['message']);
        
        self::assertArraySubset($data, Task::query()->find(1)->getAttributes());
    }

    public function testSetsTaskStatus()
    {
        $task = factory(Task::class)->create(['status' => 'View']);

        $this->post(
            "/api/tasks/{$task->getKey()}/status",
            ['status' => 'Done'], 
            $this->getAuthorizationHeaders()
        )->assertOk()->assertJsonStructure(['message']);

        $this->assertEquals('Done', $task->refresh()->status);
    }

    public function testSetsTaskUser()
    {
        $task = factory(Task::class)->create(['user_id' => $this->user->getKey()]);
        $otherUser = factory(User::class)->create();

        $this->post(
            "/api/tasks/{$task->getKey()}/user",
            ['user_id' => $otherUser->getKey()],
            $this->getAuthorizationHeaders()
        )->assertOk()->assertJsonStructure(['message']);

        $this->assertEquals($otherUser->getKey(), $task->refresh()->user_id);
    }

    public function testDeletesTask()
    {
        $task = factory(Task::class)->create();
        $this->assertNotNull(Task::query()->find($task->getKey()));
        
        $this->delete("/api/tasks/{$task->getKey()}", [], $this->getAuthorizationHeaders())
            ->assertOk()->assertJsonStructure(['message']);

        $this->assertNull(Task::query()->find($task->getKey()));
    }

    private function getAuthorizationHeaders()
    {
        return ['Authorization' => "Bearer {$this->user->api_token}"];
    }
}
