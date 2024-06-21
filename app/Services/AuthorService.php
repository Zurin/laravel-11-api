<?php

namespace App\Services;

use App\Models\Author;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthorService
{
    public function getAll()
    {
        $key = 'authors.all';

        if (Redis::exists($key)) {
            return unserialize(Redis::get($key));
        }

        try {
            $authors = Author::all();

            // Cache data for 5 minutes
            Redis::setex($key, 300, serialize($authors));

            return $authors;
        } catch (\Exception $e) {
            Log::error('Error get all author: ' . $e->getMessage());
            throw new \Exception('Failed to get all author: ' . $e->getMessage(), 500);
        }
    }

    public function get($id)
    {
        $key = 'author.' . $id;

        if (Redis::exists($key)) {
            return unserialize(Redis::get($key));
        }

        try {
            $author = Author::findOrFail($id);

            // Cache data for 5 minutes
            Redis::setex($key, 300, serialize($author));

            return $author;
        } catch (ModelNotFoundException) {
            throw new \Exception('Author with data id: ' . $id . ' not found', 500);
        } catch (\Exception $e) {
            Log::error('Error to get author: ' . $e->getMessage());
            throw new \Exception('Failed to get author: ' . $e->getMessage(), 500);
        }
    }

    public function getBooks($id)
    {
        $key = 'author.books.' . $id;

        if (Redis::exists($key)) {
            return unserialize(Redis::get($key));
        }

        try {
            $author = Author::with('books')->findOrFail($id);

            // Cache data for 5 minutes
            Redis::setex($key, 300, serialize($author));

            return $author;
        } catch (ModelNotFoundException) {
            throw new \Exception('Author with data id: ' . $id . ' not found', 500);
        } catch (\Exception $e) {
            Log::error('Error to get author: ' . $e->getMessage());
            throw new \Exception('Failed to get author: ' . $e->getMessage(), 500);
        }
    }

    public function create($data)
    {
        try {
            $author = Author::create($data);
            Redis::del('authors.all');
            return $author;
        } catch (\Exception $e) {
            Log::error('Error creating author: ' . $e->getMessage());
            throw new \Exception('Failed to create author: ' . $e->getMessage(), 500);
        }
    }

    public function update($id, $data)
    {
        try {
            $author = Author::findOrFail($id);
            $author->update($data);
            Redis::del('authors.all');
            Redis::del('author.' . $id);
            Redis::del('author.books.' . $author->id);
            return $author;
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Author with data id: ' . $id . ' not found', 500);
        } catch (\Exception $e) {
            Log::error('Error updating author: ' . $e->getMessage());
            throw new \Exception('Failed to update author: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        try {
            $author = Author::findOrFail($id);
            $author->delete();
            Redis::del('authors.all');
            Redis::del('author.' . $id);
            Redis::del('author.books.' . $author->id);
            return true;
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Author with data id: ' . $id . ' not found', 500);
        } catch (\Exception $e) {
            Log::error('Error deleting author: ' . $e->getMessage());
            throw new \Exception('Failed to delete author: ' . $e->getMessage(), 500);
        }
    }
}
