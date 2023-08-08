<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Package extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';


    protected $keyType = 'string';

    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Uuid::uuid4());
        });
    }
    public function bookings() {
        return $this->hasMany(Booking::class);
    }

    public function itinerary() {
        return $this->hasOne(Itinerary::class);
    }

    protected $fillable = ['name','description','price','start_date','end_date','available_slots'];
}
