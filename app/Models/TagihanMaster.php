<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanMaster extends Model
{
    protected $guarded = ['id'];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
