<?php

namespace Tests\Unit;

use App\Models\Author;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Redis;
use Mockery;
use Tests\TestCase;

class BookServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected $bookService;

    public function setUp(): void
    {
        parent::setUp();
        $this->bookService = new BookService();
    }

    public function testGetAllBooksFromCache()
    {
        Redis::shouldReceive('exists')
            ->once()
            ->with('books.all')
            ->andReturn(true);

        Redis::shouldReceive('get')
            ->once()
            ->with('books.all')
            ->andReturn(serialize(Author::factory()->count(3)->make()));

        $books = $this->bookService->getAll();
        $this->assertCount(3, $books);
    }

    public function testGetAllBooksFromDatabase()
    {
        Redis::shouldReceive('exists')
            ->once()
            ->with('books.all')
            ->andReturn(false);

        Redis::shouldReceive('setex')
            ->once();

        Book::factory()->count(3)->create();
        $result = $this->bookService->getAll();
        $this->assertCount(3, $result);
    }

    public function testGetBookFromCache()
    {
        $book = Book::factory()->make();

        Redis::shouldReceive('exists')
            ->once()
            ->with('book.' . $book->id)
            ->andReturn(true);

        Redis::shouldReceive('get')
            ->once()
            ->with('book.' . $book->id)
            ->andReturn(serialize($book));

        $result = $this->bookService->get($book->id);
        $this->assertEquals($book->id, $result->id);
    }

    public function testGetBookFromDatabase()
    {
        $book = Book::factory()->create();
        $result = $this->bookService->get($book->id);
        $this->assertEquals($book->id, $result->id);
    }

    public function testCreateBook()
    {
        $author = Author::factory()->create();

        Redis::shouldReceive('del')
            ->twice()
            ->with(Mockery::anyOf('books.all', 'author.books.'.$author->id));

        $bookData = ['title' => 'Test Book', 'description' => 'Lorem ipsum', 'publish_date' => '2000-01-01', 'author_id' => $author->id];

        $result = $this->bookService->create($bookData);
        $this->assertEquals('Test Book', $result->title);
    }

    public function testUpdateBook()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create();

        $bookData = ['title' => 'Test Book Update', 'description' => 'Lorem ipsum dolor sit amet', 'publish_date' => '1999-01-01', 'author_id' => $author->id];

        Redis::shouldReceive('del')
            ->times(3)
            ->with(Mockery::anyOf('books.all', 'book.' . $book->id, 'author.books.'.$author->id));

        $updatedBook = $this->bookService->update($book->id, $bookData);
        $this->assertEquals('Test Book Update', $updatedBook->title);
    }

    public function testDeleteBook()
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        Redis::shouldReceive('del')
            ->times(3)
            ->with(Mockery::anyOf(
                'books.all',
                'book.' . $book->id,
                'author.books.' . $author->id
            ));

        $result = $this->bookService->delete($book->id);
        $this->assertTrue($result);
    }

}
