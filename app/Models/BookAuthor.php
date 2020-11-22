<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookAuthor extends Model
{

    use SoftDeletes;

    protected $fillable = ['author_id', 'book_id', 'deleted'];

//    protected $primaryKey = ['author_id', 'book_id'];
//    public $incrementing = false;

    use HasFactory;
}
