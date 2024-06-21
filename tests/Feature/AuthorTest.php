<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Services\AuthorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    protected $authorService;

    public function setUp(): void
    {
        parent::setUp();
        $this->authorService = Mockery::mock(AuthorService::class);
        $this->app->instance(AuthorService::class, $this->authorService);
    }

    public function testIndex()
    {
        $this->authorService->shouldReceive('getAll')
            ->once()
            ->andReturn(Author::factory()->count(3)->make());

        $response = $this->get('/api/authors');

        $response->assertStatus(200)
            ->assertJson(['message' => 'ok']);
    }

    public function testShow()
    {
        $author = Author::factory()->make(['id' => 1]);
        $this->authorService->shouldReceive('get')
            ->once()
            ->with(1)
            ->andReturn($author);

        $response = $this->get('/api/authors/' . $author->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'ok']);
    }

    public function testShowNotFound()
    {
        Author::factory()->make(['id' => 1]);
        $this->mock(AuthorService::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with(999)
                ->andThrow(ModelNotFoundException::class, 'Author with data id: 999 not found');
        });

        $response = $this->get('/api/authors/999');

        $response->assertStatus(500)
            ->assertJson(['message' => 'Author with data id: 999 not found']);
    }

    public function testAuthorBook()
    {
        $author = Author::factory()->create();
        Book::factory()->create();

        $this->authorService->shouldReceive('getBooks')
            ->once()
            ->with($author->id)
            ->andReturn($author);

        $response = $this->get('/api/authors/' . $author->id . '/books');

        $response->assertStatus(200)
            ->assertJson(['message' => 'ok']);
    }

    public function testAuthorBookNotFound()
    {
        Author::factory()->make(['id' => 1]);
        $this->mock(AuthorService::class, function ($mock) {
            $mock->shouldReceive('getBooks')
                ->with(999)
                ->andThrow(ModelNotFoundException::class, 'Author with data id: 999 not found');
        });

        $response = $this->get('/api/authors/999/books');

        $response->assertStatus(500)
            ->assertJson(['message' => 'Author with data id: 999 not found']);
    }

    public function testStore()
    {
        $authorData = ['name' => 'Test Author', 'bio' => 'Test Bio', 'birth_date' => '2000-01-01'];
        $createdAuthor = Author::factory()->make($authorData);

        $this->authorService->shouldReceive('create')
            ->once()
            ->with($authorData)
            ->andReturn($createdAuthor);

        $response = $this->post('/api/authors', $authorData);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Author created successfully']);
    }

    public function testStoreValidation()
    {
        // Test case 1: Missing 'name' field
        $authorData = ['bio' => 'Test Bio', 'birth_date' => '2000-01-01'];

        $response = $this->post('/api/authors', $authorData);

        $response->assertStatus(422)
        ->assertJsonValidationErrors('name');

        // Test case 2: Missing 'bio' field
        $authorData = ['name' => 'Test Author', 'birth_date' => '2000-01-01'];

        $response = $this->post('/api/authors', $authorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('bio');

        // Test case 3: Missing 'birth_date' field (if birth_date is required)
        $authorData = ['name' => 'Test Author', 'bio' => 'Test Bio'];

        $response = $this->post('/api/authors', $authorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('birth_date');

        // Test case 4: Invalid 'birth_date' format
        $authorData = ['name' => 'Test Author', 'bio' => 'Test Bio', 'birth_date' => 'invalid_date_format'];

        $response = $this->post('/api/authors', $authorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('birth_date');
    }

    public function testUpdate()
    {
        $author = Author::factory()->create(['id' => 1]);
        $authorData = ['name' => 'Updated Author', 'bio' => 'Updated Bio', 'birth_date' => '2000-01-01'];

        $this->authorService->shouldReceive('update')
            ->once()
            ->with($author->id, $authorData)
            ->andReturn($author);

        $response = $this->put('/api/authors/' . $author->id, $authorData);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Author updated successfully']);
    }

    public function testUpdateValidation()
    {
        $this->mock(AuthorService::class, function ($mock) {
            $mock->shouldReceive('update')
                ->withArgs(function ($id) {
                    return $id == 999;
                })
                ->andThrow(new ModelNotFoundException("Author with data id: 999 not found"));
        });

        $author = Author::factory()->create(['id' => 1]);

        // Test case 1: Missing 'name' field
        $authorData = ['bio' => 'Test Bio', 'birth_date' => '2000-01-01'];

        $response = $this->put('/api/authors/' . $author->id, $authorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');

        // Test case 2: Missing 'bio' field
        $authorData = ['name' => 'Test Author', 'birth_date' => '2000-01-01'];

        $response = $this->put('/api/authors/' . $author->id, $authorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('bio');

        // Test case 3: Missing 'birth_date' field (if birth_date is required)
        $authorData = ['name' => 'Test Author', 'bio' => 'Test Bio'];

        $response = $this->put('/api/authors/' . $author->id, $authorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('birth_date');

        // Test case 4: Invalid 'birth_date' format
        $authorData = ['name' => 'Test Author', 'bio' => 'Test Bio', 'birth_date' => 'invalid_date_format'];

        $response = $this->put('/api/authors/' . $author->id, $authorData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('birth_date');

        // Test case 5: Author id not found
        $authorData = ['name' => 'Test Author', 'bio' => 'Test Bio', 'birth_date' => '2000-01-01'];

        $response = $this->put('/api/authors/999', $authorData);

        $response->assertStatus(500)
            ->assertJson(['message' => 'Author with data id: 999 not found']);
    }

    public function testDestroy()
    {
        $author = Author::factory()->make(['id' => 1]);

        $this->authorService->shouldReceive('delete')
            ->once()
            ->with($author->id)
            ->andReturn(true);

        $response = $this->delete('/api/authors/' . $author->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Author deleted successfully']);
    }

    public function testDestroyIDNotFound()
    {
        Author::factory()->make(['id' => 1]);

        $this->mock(AuthorService::class, function ($mock) {
            $mock->shouldReceive('delete')
                ->withArgs(function ($id) {
                    return $id == 999;
                })
                ->andThrow(new ModelNotFoundException("Author with data id: 999 not found"));
        });

        $response = $this->delete('/api/authors/999');

        $response->assertStatus(500)
            ->assertJson(['message' => 'Author with data id: 999 not found']);
    }
}
