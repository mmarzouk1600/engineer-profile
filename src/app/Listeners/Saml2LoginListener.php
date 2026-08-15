<?php

namespace App\Listeners;

use App\Models\MuStudent;
use App\Models\MuUser;
use App\Models\User;
use App\Classes\Saml\Response;
use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Saml2LoginListener
{
    private const ATTR_PATH = 'http://iam.gov.sa/claims/';
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(public UserRepository $userRepository)
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Saml2LoginEvent  $event
     * @return void
     */
    public function handle(Saml2LoginEvent $event)
    {
        $messageId = $event->getSaml2Auth()->getLastMessageId();
        $userData = $event->getSaml2User()->getAttributes();

        $nationalId = $userData[self::ATTR_PATH . 'userid'][0];
        $response = Response::formatResponse($userData);

        $beneficiaryData = $this->userRepository->isUserStudent($nationalId);
        $employeeData = $this->userRepository->isUserEmployee($nationalId);

        if(count($employeeData)) $beneficiaryData = $employeeData;

        $user = User::firstOrCreate([
            'national_id' => $nationalId
        ], [
            'name'  => $response['ar_name'],
            'type' => User::EXTERNAL,
            ...$beneficiaryData
        ]);
        Auth::login($user, true);
        abort(redirect('/'));
    }
}
