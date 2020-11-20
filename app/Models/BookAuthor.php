<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookAuthor extends Model
{
    protected $fillable = ['author_id', 'book_id', 'deleted'];

//    protected $primaryKey = ['author_id', 'book_id'];
//    public $incrementing = false;

    use HasFactory;
}
