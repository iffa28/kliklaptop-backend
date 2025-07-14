<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $table = 'service_requests';

    protected $fillable = [
        'user_id',
        'jenis_laptop',
        'deskripsi_keluhan',
        'photo',
        'status',
        'tanggal_selesai',
        'created_at',
    ];

    /**
     * Relasi ke model User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceByAdmin()
    {
        return $this->hasOne(ServiceByAdmin::class, 'service_id');
    }

    


}
