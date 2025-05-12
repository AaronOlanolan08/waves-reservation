<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 
use Carbon\Carbon; 

class Reservation extends Model
{
    public $incrementing = false;
    protected $keyType = 'string'; 
    protected $fillable = [
        'id',
        'customer_id', 
        'date', 
        'startTime', 
        'endTime', 
        'status'
    ];

    protected static function booted()
    {
        static::retrieved(function ($reservation) {
            if ($reservation->isPastDate()) {
                if ($reservation->areBillsPaid()) {
                    $reservation->markAsCompleted();
                } elseif ($reservation->hasDownpayment() || $reservation->hasPartiallyPaidBill()) {
                    $reservation->markAsCancelled();
                } elseif (!$reservation->hasDownpayment()) {
                    $reservation->markAsInvalid();
                }
            }
        });
    }

    public function isPastDate(): bool
    {
        $reservationEnd = Carbon::parse($this->date.' '.$this->endTime, 'Asia/Manila');
        return $reservationEnd->isPast();
    }

    protected function hasDownpayment(): bool
    {
        return $this->downPayment()->exists(); 
    }

    protected function areBillsPaid(): bool
    {
        if (!$this->bill) {
            return false;
        }
        return $this->bill->status === 'paid';
    }

    protected function hasPartiallyPaidBill(): bool
    {
        return $this->bill && $this->bill->status === 'partially paid';
    }

    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->save();
    }

    public function markAsInvalid(): void
    {
        $this->status = 'invalid';
        $this->save();
    }

    public function markAsCancelled(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function reservedAmenities()
    {
        return $this->hasMany(ReservedAmenity::class, 'res_num');
    }

    public function bill()
    {
        return $this->hasOne(Bill::class, 'res_num', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function downPayment()
    {
        return $this->hasOne(DownPayment::class, 'res_num', 'id')->latestOfMany('date');
    }
}