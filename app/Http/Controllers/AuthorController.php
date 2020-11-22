<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\UsersWithExtraAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Helpers\ApiCode;
use Symfony\Component\HttpFoundation\Response;

class AuthorController extends Controller
{

    /**
     * Main table with authors of the books
     */
    const AUTHOR_TABLE = 'authors';

    /**
     * Loads authors by his/her first or last name match
     *
     * @param Request $request
     * @return Response
     */
    public function loadBySubstring(Request $request): Response
    {
        $substring = request()->post('substring');
        $excludeAuthors = request()->post('authorKeys');

        $Authors = new Author();
        $queryFirstPart = $Authors->select('*');

        /**
         * If there are two words in search string - first word counts as first name and second as last name
         * If only one - processing search in both fields
         */
        if (stripos($substring, ' ') !== false) {
            $substringParts = explode(' ', $substring);
            $firstName = $substringParts[0];
            $lastName = $substringParts[1];

            $queryFirstPart = $queryFirstPart
                ->where('first_name', 'like', '%' . $firstName . '%')
                ->where('last_name', 'like', '%' . $lastName . '%');

        } else {
            $queryFirstPart = $queryFirstPart
                ->where(function ($q) use ($substring) {
                    $q->where('first_name', 'like', '%' . $substring . '%')
                        ->orWhere('last_name', 'like', '%' . $substring . '%');
                });
        }

        $authorsBySubstring = $queryFirstPart
            ->whereNotIn('id', $excludeAuthors)
            ->get()->keyBy('id');

        return RB::success(['data' => $authorsBySubstring]);
    }

    /**
     * Loads author with amount of their books
     *
     * @return Response
     */
    public function loadForAdmin()
    {
        $authors = Author::withCount('books')->get()->keyBy('id');
        return RB::success(['data' => $authors]);
    }

    /**
     * Marks book as deleted
     *
     * @param Request $request
     * @param int $authorId
     * @return Response
     */
    public function deleteById(Request $request, int $authorId): Response
    {
        $Author = new Author();
        $deleteResult = $Author->where('id', $authorId)->delete();
        if ($deleteResult) {
            return RB::success();
        }

        return RB::error(ApiCode::INTERNAL_SERVER_ERROR);
    }

    /**
     * Changes author's info
     *
     * @param Request $request
     * @return Response
     */
    public function updateAuthorData(Request $request): Response
    {
        $postData = $request->validate([
            'data' => ['required'],
            'authorId' => ['required'],
            'data.first_name' => ['required'],
            'data.last_name' => ['required'],
        ]);

        $updateResult = DB::table(self::AUTHOR_TABLE)->where('id', $postData['authorId'])->update($postData['data']);

        if ($updateResult) {
            return RB::success();
        }

        return RB::error(ApiCode::INTERNAL_SERVER_ERROR);
    }


    public function create(): Response
    {
        return RB::success();
    }
}
