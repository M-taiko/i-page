<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelPayment extends Model
{
    protected $guarded=['id']; protected $casts=['recorded_at'=>'datetime', 'amount_minor'=>'integer'];
    public function recorder() { return $this->belongsTo(User::class, 'recorded_by'); }
}
