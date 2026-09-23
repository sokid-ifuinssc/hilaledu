<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tendik extends Model
{
    protected $table = 'tendiks';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'email',
        'jabatan',
        'bagian',
        'status_kepegawaian',
        'foto',
        'is_active',
    ];

    public function getNuptkAttribute()
    {
        return $this->attributes['nip'] ?? null;
    }

    public function setNuptkAttribute($value)
    {
        $this->attributes['nip'] = $value;
    }

    public function getTugasAttribute()
    {
        return $this->attributes['bagian'] ?? null;
    }

    public function setTugasAttribute($value)
    {
        $this->attributes['bagian'] = $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
