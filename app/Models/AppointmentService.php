<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AppointmentService extends Model {protected $guarded=['id'];protected $casts=['is_active'=>'boolean','duration_minutes'=>'integer','slot_capacity'=>'integer','price_from'=>'decimal:2'];public function organization(){return $this->belongsTo(Organization::class);}public function channel(){return $this->belongsTo(Channel::class);}public function bookings(){return $this->hasMany(AppointmentBooking::class,'service_id');}}
