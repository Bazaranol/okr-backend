<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkipExtension extends Model
{
    protected $table = 'skip_extensions';

    protected $fillable = ['skip_id', 'new_end_date', 'status'];

    public function skip()
    {
        return $this->belongsTo(Skip::class);
    }
}
