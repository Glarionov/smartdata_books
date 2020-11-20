<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\UsersWithExtraAccess;
use Illuminate\Http\Request;
use App\Models\Author;

class BookController extends Controller
{

    private int $itemsPerPage = 10;

    /**
     * Load list of all books
     *
     * @param Request $request
     * @param int $lastLoadedId
     * @return array
     */
    public function loadAll(Request $request, int $lastLoadedId)
    {
        $Book = new Book();

        $books = $Book->select('id', 'name')
            ->where('deleted', '=', 0)
            ->where('id', '>', $lastLoadedId)
            ->limit($this->itemsPerPage)->get();

        $result = [];

        foreach ($books as $book) {
            $Authors = new Author();


            $bookId = $book['id'];

            $loadedAuthors = $Authors->leftJoin('book_authors', function ($bookAuthorsHandler) {

                $bookAuthorsHandler->on('book_authors.author_id', '=', 'authors.id');
            })->select('authors.*')
                ->where('book_authors.book_id', '=', $bookId)
                ->get()->toArray();

            $element = $book;
            $authors = array_combine(array_column($loadedAuthors, 'id'), $loadedAuthors);
            $element['authors'] = $authors;

            $result[] = $element;
        }

        return ['response_type' => 'ok', 'data' => $result];
    }

    /**
     *
     *
     * @param Request $request
     * @return array
     */
    public function loadById(Request $request, $bookId)
    {
        $Book = new Book();

        $bookInfo = $Book->select('*')->where('id', '=', $bookId)->where('deleted', '=', 0)->first();

        if ($bookInfo) {
            $Authors = new Author();

            $loadedAuthors = $Authors->leftJoin('book_authors', function ($bookAuthorsHandler) {

                $bookAuthorsHandler->on('book_authors.author_id', '=', 'authors.id');
            })->select('authors.*')
                ->where('book_authors.book_id', '=', $bookId)
                ->get()->toArray();
            $authors = array_combine(array_column($loadedAuthors, 'id'), $loadedAuthors);
            $bookInfo['authors'] = $authors;
        }

        return ['response_type' => 'ok', 'data' => $bookInfo];
    }

    /**
     * Marks book as deleted
     *
     * @param Request $request
     * @param int $bookId
     * @return array
     */
    public function deleteById(Request $request, $bookId)
    {
        $user = auth()->user();

        // Only certain users have access for this operation
        if ($user) {
            $userId = $user->getAuthIdentifier();
            $UsersWithExtraAccess = new UsersWithExtraAccess();
            $isExtraUser = $UsersWithExtraAccess->select('*')->where('user_id', $userId)->count();

            if (!empty($isExtraUser)) {
                $Book = new Book();
                $updateResult = $Book->where('id', $bookId)->update(['deleted' => 1]);

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
     * Changes book's info
     *
     * @param Request $request
     */
    public function updateBookData(Request $request) {
        $user = auth()->user();

        $editingBookName = request()->post('editingBookName');
        $authorsToDelete = request()->post('authorsToDelete');
        $authorsToAdd = request()->post('authorsToAdd');
        $bookId = request()->post('bookId');
        $token = request()->post('token');

        // Only certain users have access for this operation
        if ($user) {
            $userId = $user->getAuthIdentifier();
            $UsersWithExtraAccess = new UsersWithExtraAccess();
            $isExtraUser = $UsersWithExtraAccess->select('*')->where('user_id', $userId)->count();

            if (!empty($isExtraUser)) {
                $Book = new Book();
                $updateResult = $Book->where('id', $bookId)->update(['name' => $editingBookName]);

                if ($updateResult) {
                    if (!empty($authorsToDelete)) {
                        $BooksAuthors = new BookAuthor();
                        $authorsToDelete = array_keys($authorsToDelete);
                        $updateResult = $BooksAuthors->where('book_id', $bookId)->whereIn('author_id', $authorsToDelete)->delete();
                    }

                    if (!empty($authorsToAdd)) {
                        foreach ($authorsToAdd as $authorId => $value) {
                            $BooksAuthors = new BookAuthor();
                            $BooksAuthors->book_id = $bookId;
                            $BooksAuthors->author_id = $authorId;
                            $BooksAuthors->save();
                        }
                    }
                    return ['response_type' => 'ok', 'data' => $updateResult];
                } else {
                    return ['response_type' => 'book title update error'];
                }
            } else {
                return ['type' => 'warning_message', 'message' => 'user do not have access to this operation'];
            }

        } else {
            return ['type' => 'error', 'message' => 'user data loading error'];
        }
    }

    /**
     * @param Request $request
     * @return string[]
     */
    public function create(Request $request) {
        $name = request()->post('name');
        $Book = new Book();

        $Book->name = $name;
        $Book->save();

        if ($Book->id) {
            return ['response_type' => 'ok', 'data' => $Book->id];
        } else {
            return ['response_type' => 'error', 'message' => 'book creating error'];
        }

    }

}
