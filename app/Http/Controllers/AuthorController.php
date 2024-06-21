<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Services\AuthorService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthorController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="Author",
     *     title="Author",
     *     required={"id", "name", "bio", "birth_date"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="bio", type="string", example="Author biography"),
     *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
     * )
     *
     * @OA\Schema(
     *     schema="AuthorWithBooks",
     *     title="AuthorWithBooks",
     *     required={"id", "name", "bio", "birth_date", "created_at", "updated_at", "books"},
     *     @OA\Property(property="id", type="integer", example=2),
     *     @OA\Property(property="name", type="string", example="Tes 2"),
     *     @OA\Property(property="bio", type="string", example="Lorem ipsum dolor sit amet"),
     *     @OA\Property(property="birth_date", type="string", format="date", example="1996-07-22"),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-06-21T04:08:27.000000Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-06-21T04:08:27.000000Z"),
     *     @OA\Property(
     *         property="books",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/Book")
     *     ),
     * )
     */
    protected AuthorService $authorService;

    public function __construct(AuthorService $authorService)
    {
        $this->authorService = $authorService;
    }

    /**
     * @OA\Tag(
     *     name="Authors",
     *     description="Endpoints for managing authors"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/authors",
     *     summary="Get all authors",
     *     tags={"Authors"},
     *     @OA\Response(
     *         response=200,
     *         description="List of authors",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Author"))
     *     )
     * )
     */
    public function index()
    {
        try {
            $authors = $this->authorService->getAll();
            return Response::success($authors);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/authors/{id}",
     *     summary="Get an author by ID",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author details",
     *         @OA\JsonContent(ref="#/components/schemas/Author")
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $author = $this->authorService->get($id);
            return Response::success($author);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/authors/{id}/books",
     *     summary="Get an author's detail & books by author's ID",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author details",
     *         @OA\JsonContent(ref="#/components/schemas/AuthorWithBooks")
     *     )
     * )
     */
    public function getBooks($id)
    {
        try {
            $author = $this->authorService->getBooks($id);
            return Response::success($author);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/authors",
     *     summary="Create a new author",
     *     tags={"Authors"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "bio", "birth_date"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="bio", type="string", example="Author biography"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Author created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Author")
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
                'name' => 'required|string|max:100',
                'bio' => 'required|string',
                'birth_date' => 'required|date',
            ]);

            $author = $this->authorService->create($data);

            if (!$author) {
                return Response::error('Failed to create author', 500);
            }

            return Response::success($author, 'Author created successfully', 201);

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
     *     path="/api/authors/{id}",
     *     summary="Update an existing author",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "bio", "birth_date"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="bio", type="string", example="Author biography"),
     *             @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Author updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Author")
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
                'name' => 'required|string|max:100',
                'bio' => 'required|string',
                'birth_date' => 'required|date',
            ]);

            $author = $this->authorService->update($id, $data);

            if (!$author) {
                return Response::error('Failed to update author', 500);
            }

            return Response::success($author, 'Author updated successfully', 201);

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
     *     path="/api/authors/{id}",
     *     summary="Delete an author",
     *     tags={"Authors"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the author",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Author deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->authorService->delete($id);
            return Response::success(null, 'Author deleted successfully', 200);
        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}
