<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'pdf_path',
        'extracted_text'
    ];

    public function analyses()
    {
        return $this->hasMany(Analysis::class);
    }

    public function jobs()
    {
        return $this->hasManyThrough(Job::class, Analysis::class);
    }
}
