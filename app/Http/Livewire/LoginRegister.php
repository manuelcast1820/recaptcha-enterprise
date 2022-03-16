<?php

namespace App\Http\Livewire;

use Google\Cloud\RecaptchaEnterprise\V1\Key;
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings;
use Google\Cloud\RecaptchaEnterprise\V1\WebKeySettings\IntegrationType;
use Livewire\Component;

class LoginRegister extends Component
{
    public $users, $email, $password, $name;
    public $registerForm = false;

    public function render()
    {
        return view('livewire.login-register');
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
    }

    public function login()
    {

        dd(json_decode(file_get_contents(storage_path('app/workards-enterprise.json')), true));

        $client = new RecaptchaEnterpriseServiceClient(
            [
                'credentials' => json_decode(file_get_contents(storage_path('app/workards-enterprise.json')), true),
                'projectId' => 'MY_PROJECT'
              ]
        );
        $project = RecaptchaEnterpriseServiceClient::projectName('[MY_PROJECT_ID]');
        $webKeySettings = (new WebKeySettings())
            ->setAllowedDomains(['example.com'])
            ->setAllowAmpTraffic(false)
            ->setIntegrationType(IntegrationType::CHECKBOX);
        $key = (new Key())
            ->setWebSettings($webKeySettings)
            ->setDisplayName('my sample key')
            ->setName('my_key');

        $response = $client->createKey($project, $key);

        printf('Created key: %s' . PHP_EOL, $response->getName());
        $validatedDate = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // if (\Auth::attempt(array('email' => $this->email, 'password' => $this->password))) {
        //     session()->flash('message', "You are Login successful.");
        // } else {
        //     session()->flash('error', 'email and password are wrong.');
        // }
    }

    public function register()
    {
        $this->registerForm = !$this->registerForm;
    }

    public function registerStore()
    {
        $validatedDate = $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $this->password = Hash::make($this->password);

        User::create(['name' => $this->name, 'email' => $this->email, 'password' => $this->password]);

        session()->flash('message', 'Your register successfully Go to the login page.');

        $this->resetInputFields();
    }
}
