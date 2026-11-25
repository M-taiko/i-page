<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AppointmentBooking extends Model {protected $guarded=['id'];protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime','marketing_consent'=>'boolean','consent_recorded_at'=>'datetime'];public function service(){return $this->belongsTo(AppointmentService::class,'service_id');}public function channel(){return $this->belongsTo(Channel::class);}public function customer(){return $this->belongsTo(User::class,'customer_id');}public function assignee(){return $this->belongsTo(User::class,'assigned_to');}public function events(){return $this->hasMany(AppointmentEvent::class,'booking_id');}}
