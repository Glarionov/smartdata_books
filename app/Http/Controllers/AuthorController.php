<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\UsersWithExtraAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{
    /**
     * Loads authors by his/her first or last name match
     *
     * @param Request $request
     * @return array
     */
    public function loadBySubstring(Request $request)
    {
        $substring = request()->post('substring');
        $excludeAuthors = request()->post('authorKeys');

        $Authors = new Author();

        if (stripos($substring, ' ') !== false) {
            $substringParts = explode(' ', $substring);
            $firstName = $substringParts[0];
            $lastName = $substringParts[1];

            $authorsBySubstring = $Authors->select('*')
                ->where('first_name', 'like', '%' . $firstName . '%')
                ->where('last_name', 'like', '%' . $lastName . '%')
                ->whereNotIn('id', $excludeAuthors)
                ->where('deleted', '=', 0)->get()->keyBy('id');

        } else {
            $authorsBySubstring = $Authors->select('*')
                ->where(function ($q) use ($substring) {
                    $q->where('first_name', 'like', '%' . $substring . '%')
                        ->orWhere('last_name', 'like', '%' . $substring . '%');
                })
                ->whereNotIn('id', $excludeAuthors)
                ->where('deleted', '=', 0)->get()->keyBy('id');
        }

        return ['response_type' => 'ok', 'substring' => $substring, 'data' => $authorsBySubstring];
    }

    public function loadForAdmin()
    {
        $Author = new Author();
        $allAuthors = $Author->all()->keyBy('id')->toArray();

        $BookAuthor = new BookAuthor();

        $amountOfBooksByAuthor = $BookAuthor->select('author_id', DB::raw('count(*) as book_amount'))
            ->groupBy('author_id')
            ->get()->toArray();

        foreach ($allAuthors as $authorId => $author) {
            $allAuthors[$authorId]['book_amount'] =
                empty($amountOfBooksByAuthor[$authorId]) ? 0 : $amountOfBooksByAuthor[$authorId]['book_amount'];
        }

        return ['response_type' => 'ok', 'data' => $allAuthors];
    }

    /**
     * Marks book as deleted
     *
     * @param Request $request
     * @param int $authorId
     * @return array
     */
    public function deleteById(Request $request, $authorId)
    {
        $user = auth()->user();

        // Only certain users have access for this operation
        if ($user) {
            $userId = $user->getAuthIdentifier();
            $UsersWithExtraAccess = new UsersWithExtraAccess();
            $isExtraUser = $UsersWithExtraAccess->select('*')->where('user_id', $userId)->count();

            if (!empty($isExtraUser)) {
                $Author = new Author();
                $updateResult = $Author->where('id', $authorId)->update(['deleted' => 1]);

                if ($updateResult) {
                    return ['response_type' => 'ok', 'data' => ['deleted' => '1']];
                } else {
                    return ['response_type' => 'error'];
                }
            } else {
                return ['type' => 'warning_message', 'message' => 'user do not have access to this operation'];
            }

        } else {
            return ['type' => 'error', 'message' => 'user data loading error'];
        }
    }

    /**
     * Changes author's info
     *
     * @param Request $request
     */
    public function updateAuthorData(Request $request)
    {
        $user = auth()->user();

        $data = request()->post('data');
        $authorId = request()->post('authorId');

        // Only certain users have access for this operation
        if ($user) {
            $userId = $user->getAuthIdentifier();
            $UsersWithExtraAccess = new UsersWithExtraAccess();
            $isExtraUser = $UsersWithExtraAccess->select('*')->where('user_id', $userId)->count();

            if (!empty($isExtraUser)) {
                $Author = new Author();

                $updateResult = $Author->where('id', $authorId)->update($data);

                if ($updateResult) {
                    return ['response_type' => 'ok', 'data' => $updateResult];
                } else {
                    return ['response_type' => 'author update error'];
                }
            } else {
                return ['type' => 'warning_message', 'message' => 'user do not have access to this operation'];
            }

        } else {
            return ['type' => 'error', 'message' => 'user data loading error'];
        }
    }
}
