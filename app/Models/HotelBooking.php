<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelBooking extends Model
{
    protected $guarded=['id'];
    protected $casts=['check_in'=>'date','check_out'=>'date','nightly_rates'=>'array','marketing_consent'=>'boolean','consent_recorded_at'=>'datetime','total_minor'=>'integer','paid_minor'=>'integer'];
    public function room() { return $this->belongsTo(HotelRoom::class, 'room_id'); }
    public function channel() { return $this->belongsTo(Channel::class); }
    public function payments() { return $this->hasMany(HotelPayment::class, 'booking_id'); }
    public function getPaymentStatusAttribute(): string { return $this->paid_minor === 0 ? 'unpaid' : ($this->paid_minor >= $this->total_minor ? 'paid' : 'partial'); }
}
