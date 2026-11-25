<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelRoomRate extends Model { public function setDateAttribute($value): void { $this->attributes['date']=\Carbon\Carbon::parse($value)->toDateString(); } protected $guarded=['id']; protected $casts=['date'=>'date', 'rate_minor'=>'integer']; }
