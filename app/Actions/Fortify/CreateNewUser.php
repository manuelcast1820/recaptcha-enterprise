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
        // $client = new RecaptchaEnterpriseServiceClient(
        //     [
        //         'credentials' => json_decode(file_get_contents(storage_path('app/public/workards-enterprise.json')), true),
        //         'projectId' => 'workards-342116'
        //     ]
        // );
        $project = RecaptchaEnterpriseServiceClient::projectName('workards-342116');
        // $event = (new Event())
        //     ->setSiteKey('6LcFD-QeAAAAAAd_oIG03wRmXROBsmMMkZl6KJdP')
        //     ->setExpectedAction('signup')
        //     ->setToken($input['g-recaptcha-response']);

        // $assessment = (new Assessment())
        //     ->setEvent($event);
        // $response = $client->createAssessment(
        //     $project,
        //     $assessment
        // );


        $client = new RecaptchaEnterpriseServiceClient();
        $projectName = $client->projectName($project);

        $event = (new Event())
            ->setSiteKey('6LcFD-QeAAAAAAd_oIG03wRmXROBsmMMkZl6KJdP')
            ->setToken($input['g-recaptcha-response']);

        $assessment = (new Assessment())
            ->setEvent($event);

        $response = $client->createAssessment(
            $projectName,
            $assessment
        );

        dd($response);


        // $webKeySettings = (new WebKeySettings())
        //     ->setAllowedDomains(['127.0.0.1'])
        //     ->setAllowAmpTraffic(false)
        //     ->setIntegrationType(IntegrationType::CHECKBOX);
        // $key = (new Key())
        //     ->setWebSettings($webKeySettings)
        //     ->setDisplayName('recaptchaKey')
        //     ->setName('workardKey');
        // $response = $client->createKey($project, $key);
        dd($response);

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
}
