<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Google\Cloud\RecaptchaEnterprise\V1\Key;
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings\IntegrationType;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {

        $client = new RecaptchaEnterpriseServiceClient(
            [
                'credentials' => json_decode(file_get_contents(storage_path(env('GOOGLE_PATH_CREDENTIALS'))), true),
            ]
        );

        $event = (new Event())
            ->setSiteKey(env('GOOGLE_KEY_ACCOUNT'))
            ->setToken($input['g-recaptcha-response']);

        $assessment = (new Assessment())
            ->setEvent($event);

        $response = $client->createAssessment(
            env('GOOGLE_PROJECT_ID'),
            $assessment
        );

        $bot_score = $response->getRiskAnalysis()->getScore();
        dd($bot_score);
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }

    public function generateTOken()
    {
        $client = new \Google_Client;
        $client->setAuthConfig(storage_path('app/public/workards-enterprise.json'));
        $client->useApplicationDefaultCredentials();
        $client->addScope('https://www.googleapis.com/auth/cloud-platform');
        $client->fetchAccessTokenWithAssertion();
        $access_token = $client->getAccessToken();
        var_dump($access_token);

        dd($access_token);
    }
}
