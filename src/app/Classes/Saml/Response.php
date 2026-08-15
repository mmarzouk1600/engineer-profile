<?php
namespace App\Classes\Saml;

use Carbon\Carbon;
use Validator;

class Response
{
    const ATTR_PATH = 'http://iam.gov.sa/claims/';

    public static function formatResponse($response)
    {
        $response = self::formatDate($response);
        $response = self::clearArrayNames($response);
        return $response;
    }



    private static function formatDate($response)
    {
        $response[self::ATTR_PATH . 'iqamaExpiryDateGregorian'][0] =  Carbon::parse($response[self::ATTR_PATH . 'iqamaExpiryDateGregorian'][0])->format('Y/m/d');
        $response[self::ATTR_PATH . 'idExpiryDateGregorian'][0] =  Carbon::parse($response[self::ATTR_PATH . 'idExpiryDateGregorian'][0])->format('Y/m/d');
        $response[self::ATTR_PATH . 'cardIssueDateGregorian'][0] =  Carbon::parse($response[self::ATTR_PATH . 'cardIssueDateGregorian'][0])->format('Y/m/d');
        $response[self::ATTR_PATH . 'dob'][0] =  Carbon::parse($response[self::ATTR_PATH . 'dob'][0])->format('Y/m/d');
        return $response;
    }

    private static function clearArrayNames($response)
    {
        $validated_response = [
            'user_id' => $response[self::ATTR_PATH . 'userid'][0],
            'ar_family_name' => $response[self::ATTR_PATH . 'arabicFamilyName'][0],
            //'en_family_name' => $response[self::ATTR_PATH . 'englishFamilyName'][0],
            'ar_father_name' => $response[self::ATTR_PATH . 'arabicFatherName'][0],
            //'en_father_name' => $response[self::ATTR_PATH . 'englishFatherName'][0],
            'ar_name' => $response[self::ATTR_PATH . 'arabicName'][0],
            //'en_name' => $response[self::ATTR_PATH . 'englishName'][0],
            'ar_first_name' => $response[self::ATTR_PATH . 'arabicFirstName'][0],
            //'en_first_name' => $response[self::ATTR_PATH . 'englishFirstName'][0],
            'ar_grandfather_name' => $response[self::ATTR_PATH . 'arabicGrandFatherName'][0],
            //'en_grandfather_name' => $response[self::ATTR_PATH . 'englishGrandFatherName'][0],
            'ar_nationality' => $response[self::ATTR_PATH . 'arabicNationality'][0],
            'nationality' => $response[self::ATTR_PATH . 'nationality'][0],
            'nationality_code' => $response[self::ATTR_PATH . 'nationalityCode'][0],
            'gender' => $response[self::ATTR_PATH . 'gender'][0],
            'card_issue_date' => $response[self::ATTR_PATH . 'cardIssueDateGregorian'][0],
            'card_issue_hijri' => $response[self::ATTR_PATH . 'cardIssueDateHijri'][0],
            'iqama_expiry_date' => $response[self::ATTR_PATH . 'iqamaExpiryDateGregorian'][0],
            'iqama_expiry_hijri' => $response[self::ATTR_PATH . 'iqamaExpiryDateHijri'][0],
            'id_expiry_date' => $response[self::ATTR_PATH . 'idExpiryDateGregorian'][0],
            'id_expiry_hijri' => $response[self::ATTR_PATH . 'idExpiryDateHijri'][0],
            'birth_date' => $response[self::ATTR_PATH . 'dob'][0],
            'birth_hijri' => $response[self::ATTR_PATH . 'dobHijri'][0],

        ];
        return $validated_response;
      }

}
