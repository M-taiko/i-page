<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AppointmentEvent extends Model {protected $guarded=['id'];protected $casts=['customer_visible'=>'boolean'];public function actor(){return $this->belongsTo(User::class,'actor_id');}public function booking(){return $this->belongsTo(AppointmentBooking::class,'booking_id');}}
