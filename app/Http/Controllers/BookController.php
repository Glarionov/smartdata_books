<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookAuthor;
use App\Models\UsersWithExtraAccess;
use http\Client\Response;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use App\Helpers\ApiCode;

class BookController extends Controller
{
    /**
     * Maximum of books getting per load
     * @var int
     */
    private int $itemsPerPage = 10;

    /**
     * Table connecting books and its' authors
     */
    const BOOK_AUTHORS_TABLE = 'book_authors';

    /**
     * Load list of all books from certain ID
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

        foreach ($books as $bookId => $book) {
            $authors = $book->authors()->get()->keyBy('id');
            $result[$bookId]['authors'] = $authors;
        }

        return RB::success(['data' => $result]);
    }

    /**
     * Loads books's info by id
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
    public function updateBookData(Request $request)
    {
        $request->validate([
            'bookId' => ['required'],
        ]);

        $editingBookName = $request->post('editingBookName', '');
        $authorsToDelete = $request->post('authorsToDelete', []);
        $authorsToAdd = $request->post('authorsToAdd', []);
        $bookId = $request->post('bookId');

        $Book = new Book();
        $updateResult = false;

        if ($editingBookName) {
            $updateResult = $Book->where('id', $bookId)->update(['name' => $editingBookName]);
        }

        if ($updateResult || !$editingBookName) {
            if (!empty($authorsToDelete)) {
                $BooksAuthors = new BookAuthor();
                $authorsToDeleteIds = array_keys($authorsToDelete);
                $BooksAuthors->where('book_id', $bookId)
                    ->whereIn('author_id', $authorsToDeleteIds)->delete();
            }

            if (!empty($authorsToAdd)) {
                $insertData = [];
                foreach ($authorsToAdd as $authorId => $value) {
                    $insertData[] = [
                        'author_id' => $authorId,
                        'book_id' => $bookId
                    ];
                }
                DB::table(self::BOOK_AUTHORS_TABLE)->insert($insertData);
            }
            return RB::success();
        } else {
            return RB::error(ApiCode::INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Creates new Book
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
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
