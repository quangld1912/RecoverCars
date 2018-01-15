<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mitni\Google\GoogleSpreadsheet;

class recoverCarsBySpreadsheet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recoverSpreadsheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recovery Cars By SpreadSheet Google';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sheet_id = env('SHEET_ID');
        if (php_sapi_name() != 'cli') {
            throw new Exception('This application must be run on the command line.');
        }
        // Get the API client and construct the service object.
        $client = $this->getClient();
        $service = new \Google_Service_Sheets($client);

        $options = array('valueInputOption' => 'RAW');
        $values = [
            ["Name", "Roll No.", "Contact"],
            ["Anis", "001", "+88017300112233"],
            ["Ashik", "002", "+88017300445566"]
        ];
        $body   = new \Google_Service_Sheets_ValueRange(['values' => $values]);

        $result = $service->spreadsheets_values->update($sheet_id, 'A1:C3', $body, $options);
        print($result->updatedRange. PHP_EOL);
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    function getClient() {
        $application_name = env('APPLICATION_NAME');
        $scopes = implode(' ', [\Google_Service_Sheets::SPREADSHEETS]);
        $client_secret_path = storage_path('app/client_secret.json');
        $credentials_path = '~/.credentials/sheets.googleapis.com-php-quickstart.json';

        $client = new \Google_Client();
        $client->setApplicationName($application_name);
        $client->setScopes($scopes);
        $client->setAuthConfig($client_secret_path);
        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $credentialsPath = $this->expandHomeDirectory($credentials_path);
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            // Store the credentials to disk.
            if(!file_exists(dirname($credentialsPath))) {
                mkdir(dirname($credentialsPath), 0700, true);
            }
            file_put_contents($credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    /**
     * Expands the home directory alias '~' to the full path.
     * @param string $path the path to expand.
     * @return string the expanded path.
     */
    function expandHomeDirectory($path) {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }
}
