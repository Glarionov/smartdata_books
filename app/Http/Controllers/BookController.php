<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\UsersWithExtraAccess;
use http\Client\Response;
use Illuminate\Http\Request;
use App\Models\Author;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Helpers\ApiCode;

class BookController extends Controller
{

    /**
     * Table connecting by id books and it's author
     */
    const BOOK_AUTHOR_TABLE = 'book_authors';

    private int $itemsPerPage = 10;

    /**
     * Load list of all books
     *
     * @param Request $request
     * @param int $lastLoadedId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadFromId(Request $request, int $lastLoadedId)
    {
        $Book = new Book();

        $books = $Book->select('id', 'name')
            ->where('id', '>', $lastLoadedId)
            ->limit($this->itemsPerPage)->get()->keyBy('id');

        $result = $books->toArray();



//        $ba = $books->authors()->get();
//
//        $baa = (array) $ba;
//        /*s*/echo '$baa= <pre>' . print_r($baa, true). '</pre>'; //todo r
//        exit;

        foreach ($books as $bookId => $book) {
            $authors = $book->authors()->get()->keyBy('id');
            $result[$bookId]['authors'] = $authors;

//            $Authors = new Author();
//
//
//            $bookId = $book['id'];
//
//            $loadedAuthors = $Authors->leftJoin(self::BOOK_AUTHOR_TABLE, function ($bookAuthorsHandler) {
//
//                $bookAuthorsHandler->on('book_authors.author_id', '=', 'authors.id');
//            })->select('authors.*')
//                ->where('book_authors.book_id', '=', $bookId)
//                ->get()->keyBy('id')->toArray();
//
//            $element = $book;
//            $authors = array_combine(array_column($loadedAuthors, 'id'), $loadedAuthors);
////            $element['authors'] = $authors;
//            $element['authors'] = $loadedAuthors;
//
//            $result[] = $element;
        }

        return RB::success(['data' => $result]);
    }

    /**
     *
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadById(Request $request, $bookId)
    {

        $book = Book::find($bookId);
        $book['authors'] = $book->authors()->get()->keyBy('id');

        if (!$book) {
            return RB::error(ApiCode::INTERNAL_SERVER_ERROR);
        }
        return RB::success(['data' => $book]);
    }

    /**
     * Marks book as deleted
     *
     * @param Request $request
     * @param int $bookId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteById(Request $request, $bookId)
    {
        $Book = new Book();
        $deleteResult = $Book->where('id', $bookId)->delete();
        if ($deleteResult) {
            return RB::success();
        }

        return RB::error(ApiCode::INTERNAL_SERVER_ERROR);
    }

    /**
     * Changes book's info
     *
     * @param Request $request
     */
    public function updateBookData(Request $request) {

        /*
                 $data = $this->validate($request, [
            "name" => 'required|string|max:255',
            "last_name" => 'required|string|max:255',
        ]);
        $author->update($data);
        $author->books()->attach(Book::find($request->books_id));
         */
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
     * Creates new Book
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request) {
        $postData = $request->validate([
            'name' => ['required', 'string']
        ]);

        $Book = new Book();

        $Book->name = $postData['name'];
        $Book->save();

        if ($Book->id) {
            return RB::success(['id' => $Book->id]);
        } else {
            return RB::error(ApiCode::INTERNAL_SERVER_ERROR);
        }
    }

}
