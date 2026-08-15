<?php
/**
 * @author Mohamed Marzouk <mohamedmarzouk1600@gmail.com>
 * @Copyright Maximum Develop
 * @FileCreated 10/18/19 10:01 AM
 * @Contact https://www.linkedin.com/in/mohamed-marzouk-138158125
 */

namespace App\Enums;

class UserGroupType extends Enum
{
    public const superadmin = 1;

    public const admin = 2;

    public const supervisor = 3;
    public const coordinator = 4;
    public const student = 5;
    public const certified = 6;
    public const GRADUATE = 7;
}
