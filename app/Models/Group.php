<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $fillable = ['group_number', 'course'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
