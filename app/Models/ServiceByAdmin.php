<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceByAdmin extends Model
{
    use HasFactory;

    protected $table = 'service_by_admin';

    protected $fillable = [
        'service_id',
        'nama_servis',
        'biaya_servis',
        'total_bayar',
        'bukti_pembayaran',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_id');
    }

    public function serviceSpareparts()
    {
        return $this->hasMany(ServiceSparepart::class);
    }

    public function hitungTotalBayar()
    {
        $totalSparepart = $this->serviceSpareparts()->with('sparepart')->get()->sum(function ($item) {
            return (int) $item->sparepart->harga_satuan;
        });

        $this->total_bayar = $this->biaya_servis + $totalSparepart;
        $this->save();
    }
}
