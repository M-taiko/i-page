<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HotelUnit extends Model
{
    protected $guarded = ['id'];
    public function organization() { return $this->belongsTo(Organization::class); }
    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
    public function channel() { return $this->belongsTo(Channel::class); }
}
