<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skip extends Model
{
    protected $table = 'skips';

    protected $fillable = ['user_id', 'start_date', 'end_date',
        'status', 'document_paths', 'is_extended', 'reason'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
