<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrivateInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'label', 'code', 'signup', 'visit', 'start_date', 'end_date', 'status'
    ];

    /**
     *
     * Check Stages status
     *
     * @version 1.8.0
     * @since 1.8
     * @param string|null $invite_code
     * @return Model|null
     */
    public static function stage_status_chk($invite_code = null)
    {
        $currentDate = Carbon::now()->toDateTimeString();
        $inv_code = self::where('code', $invite_code)->where('status', 'active')
            ->where(function ($query) use ($currentDate) {
                $query->where('start_date', '<=', $currentDate)
                    ->orWhereNull('start_date');
            })->where(function ($query) use ($currentDate) {
                $query->where('end_date', '>', $currentDate)
                    ->orWhereNull('end_date');
            })->first();
        
        return $inv_code;
    }
}
