<?php

namespace Tests\Feature;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\User;

class UserTest extends TestCase
{
    // PHPUnit 8 deprecates assertArraySubset method,
    // so we'll use an external package.
    use ArraySubsetAsserts;

    use RefreshDatabase;
    use WithFaker;

    private const USER_JSON_STRUCTURE = [
        'user_id', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'
    ];

    private const LIST_RESPONSE_STRUCTURE = [
        'current_page',
        'data' => ['*' => self::USER_JSON_STRUCTURE],
        'first_page_url', 'from', 'last_page', 'last_page_url',
        'next_page_url', 'path', 'per_page', 'prev_page_url', 'to', 'total'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        factory(User::class, 20)->create();
    }

    public function testReturnsPaginatedUserListWithTenRecordsPerPage()
    {
        $response = $this->get('/api/users?page=2');

        $response
            ->assertOk()
            ->assertJsonStructure(self::LIST_RESPONSE_STRUCTURE)
            ->assertJsonPath('current_page', 2);
        $this->assertEquals(10, count($response['data']));
    }

    public function testReturnsPaginatedUserListForTheFirstPageAsDefault()
    {
        $this->get('/api/users')->assertJsonPath('current_page', 1);
    }

    public function testReturnsValidListForPageThatIsOutOfBounds()
    {
        $leftResponse = $this->get('api/users?page=0');
        
        $leftResponse->assertJsonStructure(self::LIST_RESPONSE_STRUCTURE);
        $this->assertNotEmpty($leftResponse['data']);

        $rightOutOfBoundPage = $leftResponse['last_page'] + 1;
        $rightResponse = $this->get("/api/users?page=$rightOutOfBoundPage");

        $rightResponse->assertJsonStructure(self::LIST_RESPONSE_STRUCTURE);
        $this->assertEmpty($rightResponse['data']);
    }

    public function testCreatesUser()
    {
        $user = factory(User::class)->make();
        
        $response = $this->post('/api/users', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'password' => $this->faker->password(32)
        ]);
        
        $response->assertCreated()->assertJsonStructure(self::USER_JSON_STRUCTURE);
        $this->assertNotNull(User::query()->find($response['user_id']));
    }

    public function testReturnsValidationErrorsWhenCreatesUser()
    {
        // For example we will not send any post data.
        $this->post('/api/users', [])->assertJsonValidationErrors([
            'first_name', 'last_name', 'email', 'password'
        ]);
    }

    public function testReturnsUserInfo()
    {
        $user = factory(User::class)->create();
        
        $this->get("/api/users/{$user->user_id}")->assertOk()->assertJson([
            'user_id' => $user->user_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'created_at' => $user->created_at->toJSON(),
            'updated_at' => $user->updated_at->toJSON()
        ]);
    }

    public function testReturnsNotFoundWhenRequestingUserDoesNotExist()
    {
        $this->get('/api/user/0')->assertNotFound();
    }

    public function testDoesLoginAndReturnsToken()
    {
        $this->post('/api/profile/login', [
            'email' => $this->createUserWithPassword('123')->email,
            'password' => '123'
        ])->assertOk()->assertJsonStructure(['api_token']);
    }

    public function testReturnsValidationErrorsWhenDoingLogin()
    {
        $this->post('/api/profile/login', [
            'email' => $this->createUserWithPassword('123')->email,
            'password' => '456'
        ])->assertJsonValidationErrors(['common']);
    }

    public function testDoesEditingOwnProfile()
    {
        $user = $this->createUserWithPassword('123');
        $data = [
            'first_name' => 'User',
            'last_name' => 'Edited',
            'email' => 'edited' . Str::random('6') . '@edited.com',
            'password' => '456'
        ];
        
        $this->post('/api/profile/edit', $data, $this->getAuthorizationHeaders($user))
            ->assertOk()->assertJsonStructure(['message', 'new_token']);
        
        $user->refresh();

        self::assertArraySubset(
            array_filter($data, function ($key) { $key !== 'password'; }, ARRAY_FILTER_USE_KEY),
            $user->getAttributes()
        );
        $this->assertTrue(User::checkPassword('456', $user->password));
    }

    public function testEditingProfileWithoutAuthorizationReturnsUnathorizedError()
    {
        $this->post('/api/profile/edit')->assertUnauthorized();
    }

    public function testDeletesOwnProfile()
    {
        $user = $this->createUserWithPassword('123');
        $this->assertNotNull(User::query()->find($user->getKey()));
        
        $this->delete('/api/profile', [], $this->getAuthorizationHeaders($user))->assertOk();
        
        $this->assertNull(User::query()->find($user->getKey()));
    }

    private function createUserWithPassword(string $password): User
    {
        return factory(User::class)->create([
            'password' => User::hashPassword($password)
        ]);
    }

    private function getAuthorizationHeaders(User $user)
    {
        return ['Authorization' => "Bearer {$user->api_token}"];
    }
}