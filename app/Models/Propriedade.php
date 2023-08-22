<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Propriedade extends Model
{
    use HasFactory;

    protected $fillable = ['nome'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function endereco()
    {
        return $this->morphOne(Endereco::class, 'addressable');
    }
}
