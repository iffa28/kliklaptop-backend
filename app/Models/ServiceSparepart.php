<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSparepart extends Model
{
    use HasFactory;

    protected $table = 'service_sparepart';

    protected $fillable = [
        'service_by_admin_id',
        'sparepart_id',
    ];

    // Relasi ke ServiceByAdmin
    public function serviceByAdmin()
    {
        return $this->belongsTo(ServiceByAdmin::class);
    }

    // Relasi ke Sparepart
    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }
}
