<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Services\BookService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class BookController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="Book",
     *     title="Book",
     *     required={"id", "title", "description", "publish_date", "author_id"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="title", type="string", example="Example Book"),
     *     @OA\Property(property="description", type="string", example="Book description"),
     *     @OA\Property(property="publish_date", type="string", format="date", example="2024-06-21"),
     *     @OA\Property(property="author_id", type="integer", example=1),
     * )
     */

    protected BookService $bookService;

    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }
    /**
     * @OA\Tag(
     *     name="Books",
     *     description="Endpoints for managing books"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/books",
     *     summary="Get all books",
     *     tags={"Books"},
     *     @OA\Response(
     *         response=200,
     *         description="List of books",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Book"))
     *     )
     * )
     */
    public function index()
    {
        try {
            $books = $this->bookService->getAll();
            return Response::success($books);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/books/{id}",
     *     summary="Get a book by ID",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book details",
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $book = $this->bookService->get($id);
            return Response::success($book);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/books",
     *     summary="Create a new book",
     *     tags={"Books"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "publish_date", "author_id"},
     *             @OA\Property(property="title", type="string", example="Example Book"),
     *             @OA\Property(property="description", type="string", example="Book description"),
     *             @OA\Property(property="publish_date", type="string", format="date", example="2024-06-21"),
     *             @OA\Property(property="author_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Book created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:100',
                'description' => 'required|string',
                'publish_date' => 'required|date',
                'author_id' => 'required|integer|exists:authors,id',
            ]);

            $book = $this->bookService->create($data);

            if (!$book) {
                return Response::error('Failed to create book', 500);
            }

            return Response::success($book, 'Book created successfully', 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->validator->errors()->toArray()
            ], 422);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/books/{id}",
     *     summary="Update an existing book",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "publish_date", "author_id"},
     *             @OA\Property(property="title", type="string", example="Example Book"),
     *             @OA\Property(property="description", type="string", example="Book description"),
     *             @OA\Property(property="publish_date", type="string", format="date", example="2024-06-21"),
     *             @OA\Property(property="author_id", type="integer", example=1),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Book updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Book")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:100',
                'description' => 'required|string',
                'publish_date' => 'required|date',
                'author_id' => 'required|integer|exists:authors,id',
            ]);

            $book = $this->bookService->update($id, $data);

            if (!$book) {
                return Response::error('Failed to update book', 500);
            }

            return Response::success($book, 'Book updated successfully', 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->validator->errors()->toArray()
            ], 422);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/books/{id}",
     *     summary="Delete a book",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the book",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->bookService->delete($id);
            return Response::success(null, 'Book deleted successfully', 200);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
