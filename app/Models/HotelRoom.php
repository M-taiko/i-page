<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelRoom extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['is_active'=>'boolean', 'capacity'=>'integer', 'base_rate_minor'=>'integer'];
    public function organization() { return $this->belongsTo(Organization::class); }
    public function rates() { return $this->hasMany(HotelRoomRate::class, 'room_id'); }
    public function nights() { return $this->hasMany(HotelRoomNight::class, 'room_id'); }
}
