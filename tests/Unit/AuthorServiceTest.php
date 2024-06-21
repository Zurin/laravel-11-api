<?php

namespace Tests\Unit;

use App\Models\Author;
use App\Models\Book;
use App\Services\AuthorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class AuthorServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $authorService;

    public function setUp(): void
    {
        parent::setUp();
        $this->authorService = new AuthorService();
    }

    public function testGetAllAuthorsFromCache()
    {
        Redis::shouldReceive('exists')
            ->once()
            ->with('authors.all')
            ->andReturn(true);

        Redis::shouldReceive('get')
            ->once()
            ->with('authors.all')
            ->andReturn(serialize(Author::factory()->count(3)->make()));

        $authors = $this->authorService->getAll();
        $this->assertCount(3, $authors);
    }

    public function testGetAllAuthorsFromDatabase()
    {
        Redis::shouldReceive('exists')
            ->once()
            ->with('authors.all')
            ->andReturn(false);

        Redis::shouldReceive('setex')
            ->once();

        $authors = Author::factory()->count(3)->create();
        $result = $this->authorService->getAll();
        $this->assertCount(3, $result);
    }

    public function testGetAuthorFromCache()
    {
        $author = Author::factory()->make();

        Redis::shouldReceive('exists')
            ->once()
            ->with('author.' . $author->id)
            ->andReturn(true);

        Redis::shouldReceive('get')
            ->once()
            ->with('author.' . $author->id)
            ->andReturn(serialize($author));

        $result = $this->authorService->get($author->id);
        $this->assertEquals($author->id, $result->id);
    }

    public function testGetAuthorFromDatabase()
    {
        $author = Author::factory()->create();
        $result = $this->authorService->get($author->id);
        $this->assertEquals($author->id, $result->id);
    }

    public function testGetAuthorBooksFromCache()
    {
        $author = Author::factory()->make();
        Book::factory()->create();

        Redis::shouldReceive('exists')
            ->once()
            ->with('author.books.' . $author->id)
            ->andReturn(true);

        Redis::shouldReceive('get')
            ->once()
            ->with('author.books.' . $author->id)
            ->andReturn(serialize($author));

        $result = $this->authorService->getBooks($author->id);
        $this->assertEquals($author->id, $result->id);
    }

    public function testGetAuthorBooksFromDatabase()
    {
        $author = Author::factory()->create();
        Book::factory()->create();
        $result = $this->authorService->getBooks($author->id);
        $this->assertEquals($author->id, $result->id);
    }

    public function testCreateAuthor()
    {
        $authorData = ['name' => 'Test Author', 'bio' => 'Test Bio', 'birth_date' => '2000-01-01'];

        Redis::shouldReceive('del')
            ->once()
            ->with('authors.all');

        $author = $this->authorService->create($authorData);
        $this->assertEquals('Test Author', $author->name);
    }

    public function testUpdateAuthor()
    {
        $author = Author::factory()->create();
        $authorData = ['name' => 'Updated Author', 'bio' => 'Updated Bio', 'birth_date' => '2000-01-01'];

        Redis::shouldReceive('del')
            ->times(3)
            ->with(Mockery::anyOf('authors.all', 'author.' . $author->id, 'author.books.'.$author->id));

        $updatedAuthor = $this->authorService->update($author->id, $authorData);
        $this->assertEquals('Updated Author', $updatedAuthor->name);
    }

    public function testDeleteAuthor()
    {
        $author = Author::factory()->create();

        Redis::shouldReceive('del')
            ->times(3)
            ->with(Mockery::anyOf('authors.all', 'author.' . $author->id, 'author.books.'.$author->id));

        $result = $this->authorService->delete($author->id);
        $this->assertTrue($result);
    }
}
