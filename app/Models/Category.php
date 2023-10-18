<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
        
    protected $fillable = ['name' , 'group_id'];

    public function group() 
    {
        return $this->belongsTo(Group::class);
    }

    public function genres()
    {
        return $this->hasMany(Genre::class);
    }

    public static function rules()
    {
        return [
            'name' => ['required', 'string', 'between:2,100'],
        ];
    }
}
