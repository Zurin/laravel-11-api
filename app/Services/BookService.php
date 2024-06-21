<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BookService
{
    public function getAll()
    {
        $key = 'books.all';

        if (Redis::exists($key)) {
            return unserialize(Redis::get($key));
        }

        try {
            $books = Book::all();

            // Cache data for 5 minutes
            Redis::setex($key, 300, serialize($books));

            return $books;
        } catch (\Exception $e) {
            Log::error('Error get all books: ' . $e->getMessage());
            throw new \Exception('Failed to get all books: ' . $e->getMessage(), 500);
        }
    }

    public function get($id)
    {
        $key = 'book.' . $id;

        if (Redis::exists($key)) {
            return unserialize(Redis::get($key));
        }

        try {
            $book = Book::findOrFail($id);

            // Cache data for 5 minutes
            Redis::setex($key, 300, serialize($book));

            return $book;
        } catch (ModelNotFoundException) {
            throw new \Exception('Book with data id: ' . $id . ' not found', 500);
        } catch (\Exception $e) {
            Log::error('Error to get book: ' . $e->getMessage());
            throw new \Exception('Failed to get book: ' . $e->getMessage(), 500);
        }
    }

    public function create($data)
    {
        try {
            $book = Book::create($data);
            Redis::del('books.all');
            Redis::del('author.books.' . $book->author_id);
            return $book;
        } catch (\Exception $e) {
            Log::error('Error creating book: ' . $e->getMessage());
            throw new \Exception('Failed to create book: ' . $e->getMessage(), 500);
        }
    }

    public function update($id, $data)
    {
        try {
            $book = Book::findOrFail($id);
            $book->update($data);
            Redis::del('books.all');
            Redis::del('book.' . $id);
            Redis::del('author.books.' . $book->author_id);
            return $book;
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Book with data id: ' . $id . ' not found', 500);
        } catch (\Exception $e) {
            Log::error('Error updating book: ' . $e->getMessage());
            throw new \Exception('Failed to update book: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            $book = Book::findOrFail($id);
            $book->delete();
            Redis::del('books.all');
            Redis::del('book.' . $id);
            Redis::del('author.books.' . $book->author_id);
            return true;
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Book with data id: ' . $id . ' not found', 500);
        } catch (\Exception $e) {
            Log::error('Error deleting book: ' . $e->getMessage());
            throw new \Exception('Failed to delete book: ' . $e->getMessage(), 500);
        }
    }
}
