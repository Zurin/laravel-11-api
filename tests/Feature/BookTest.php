<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    protected $bookService;

    public function setUp(): void
    {
        parent::setUp();
        $this->bookService = Mockery::mock(BookService::class);
        $this->app->instance(BookService::class, $this->bookService);
    }

    public function testIndex()
    {
        $this->bookService->shouldReceive('getAll')
            ->once()
            ->andReturn(Book::factory()->count(3)->make());

        $response = $this->get('/api/books');

        $response->assertStatus(200)
            ->assertJson(['message' => 'ok']);
    }

    public function testShow()
    {
        $author = Book::factory()->make(['id' => 1]);
        $this->bookService->shouldReceive('get')
            ->once()
            ->with(1)
            ->andReturn($author);

        $response = $this->get('/api/books/' . $author->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'ok']);
    }

    public function testShowNotFound()
    {
        Book::factory()->make(['id' => 1]);
        $this->mock(BookService::class, function ($mock) {
            $mock->shouldReceive('get')
                ->with(999)
                ->andThrow(ModelNotFoundException::class, 'Book with data id: 999 not found');
        });

        $response = $this->get('/api/books/999');

        $response->assertStatus(500)
            ->assertJson(['message' => 'Book with data id: 999 not found']);
    }

    public function testStore()
    {
        $author = Author::factory()->create();
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'publish_date' => '2000-01-01', 'author_id' => $author->id];
        $createdBook = Book::factory()->make($bookData);

        $this->bookService->shouldReceive('create')
            ->once()
            ->with($bookData)
            ->andReturn($createdBook);

        $response = $this->post('/api/books', $bookData);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Book created successfully']);
    }

    public function testStoreValidation()
    {
        $author = Author::factory()->create();

        // Test case 1: Missing 'title' field
        $bookData = ['description' => 'Lorem ipsum', 'publish_date' => '2000-01-01', 'author_id' => $author->id];

        $response = $this->post('/api/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');

        // Test case 2: Missing 'description' field
        $bookData = ['title' => 'Test Book', 'publish_date' => '2000-01-01', 'author_id' => $author->id];

        $response = $this->post('/api/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('description');

        // Test case 3: Missing 'publish_date' field
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'author_id' => $author->id];

        $response = $this->post('/api/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('publish_date');

        // Test case 4: Invalid 'publish_date' format
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'publish_date' => 'invalid date here', 'author_id' => $author->id];

        $response = $this->post('/api/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('publish_date');

        // Test case 5: Missing 'author_id' field
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'publish_date' => '2000-01-01'];

        $response = $this->post('/api/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('author_id');

        // Test case 6: Invalid 'author_id' field
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'publish_date' => '2000-01-01', 'author_id' => 999];

        $response = $this->post('/api/books', $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('author_id');
    }

    public function testUpdate()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create();
        $bookData = ['title' => 'Test Book Update', 'description' => 'Lorem ipsum', 'publish_date' => '2000-01-01', 'author_id' => $author->id];

        $this->bookService->shouldReceive('update')
            ->once()
            ->with($book->id, $bookData)
            ->andReturn($book);

        $response = $this->put('/api/books/' . $book->id, $bookData);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Book updated successfully']);
    }

    public function testUpdateValidation()
    {
        $this->mock(BookService::class, function ($mock) {
            $mock->shouldReceive('update')
                ->withArgs(function ($id) {
                    return $id == 999;
                })
                ->andThrow(new ModelNotFoundException("Book with data id: 999 not found"));
        });

        $author = Author::factory()->create();
        $book = Book::factory()->create();

        // Test case 1: Missing 'title' field
        $bookData = ['description' => 'Lorem ipsum', 'publish_date' => '2000-01-01', 'author_id' => $author->id];

        $response = $this->put('/api/books/'.$book->id, $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');

        // Test case 2: Missing 'description' field
        $bookData = ['title' => 'Test Book', 'publish_date' => '2000-01-01', 'author_id' => $author->id];

        $response = $this->put('/api/books/'.$book->id, $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('description');

        // Test case 3: Missing 'publish_date' field
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'author_id' => $author->id];

        $response = $this->put('/api/books/'.$book->id, $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('publish_date');

        // Test case 4: Invalid 'publish_date' format
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'publish_date' => 'invalid date here', 'author_id' => $author->id];

        $response = $this->put('/api/books/'.$book->id, $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('publish_date');

        // Test case 5: Missing 'author_id' field
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'publish_date' => '2000-01-01'];

        $response = $this->put('/api/books/'.$book->id, $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('author_id');

        // Test case 6: Invalid 'author_id' field
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'publish_date' => '2000-01-01', 'author_id' => 999];

        $response = $this->put('/api/books/'.$book->id, $bookData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('author_id');

        // Test case 5: Book id not found
        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'publish_date' => '2000-01-01', 'author_id' => $author->id];

        $response = $this->put('/api/books/999', $bookData);

        $response->assertStatus(500)
            ->assertJson(['message' => 'Book with data id: 999 not found']);
    }

    public function testDestroy()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create();

        $this->bookService->shouldReceive('delete')
            ->once()
            ->with($book->id)
            ->andReturn(true);

        $response = $this->delete('/api/books/' . $book->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book deleted successfully']);
    }

    public function testDestroyIDNotFound()
    {
        Author::factory()->create();
        Book::factory()->create();

        $this->mock(BookService::class, function ($mock) {
            $mock->shouldReceive('delete')
                ->withArgs(function ($id) {
                    return $id == 999;
                })
                ->andThrow(new ModelNotFoundException("Book with data id: 999 not found"));
        });

        $response = $this->delete('/api/books/999');

        $response->assertStatus(500)
            ->assertJson(['message' => 'Book with data id: 999 not found']);
    }
}
