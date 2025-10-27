<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'pdf_path',
        'extracted_text'
    ];

    public function analyses()
    {
        return $this->hasMany(Analysis::class);
    }

    public function candidates()
    {
        return $this->hasManyThrough(Candidate::class, Analysis::class);
    }
}
