<?php
/**
 * @author Mohamed Marzouk <mohamedmarzouk1600@gmail.com>
 * @Copyright Maximum Develop
 * @FileCreated 04 ON 09 Oct 2018
 * @Contact https://www.linkedin.com/in/mohamed-marzouk-138158125
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Activitylog\Traits\LogsActivity;

abstract class BaseUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity, HasPushSubscriptions   ;

    protected $fillable = [
        'name',
        'email',
        'employee_id',
        'phone',
        'office_phone',
        'building_no',
        'office_no',
        'completed_at',
        'national_id',
        'type',
        'password',
        'department_id',
        'faculty_id',
        'is_student',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    const SuperAdmin = 1;

    const Admin = 2;

    const Dean = 3;
    const Coordinator = 4;
    const Student = 5;


}
