<?php

use CRM_Simplezmaleadgen_ExtensionUtil as E;

class CRM_Simplezmaleadgen_Utils
{
    private static $accessToken;
    private static $tokenExpiresAt;

//    public const SAVE_LOG = 'save_log';
    public const SAVE_LOG = [
        'slug' => 'save_log',
        'name' => 'Save Log',
        'description' => "Write debugging output to CiviCRM log file"];

//    public const SEND_CONTACT = 'send_contact';
    public const SEND_CONTACT = [
        'slug' => 'send_contact',
        'name' => 'Send Contribution Contact to Zoho',
        'description' => "Send contribution contacts as leads to Zoho Marketing Automation.\n"
            . "Only for contributions marked as completed\n"
            . "and having contributor contact email\n"
            . "the contact will be sent to Zoho MA as leads.\n"
            . "New Contacts and Present Contacts will be added\n"
            . "to the corresponding lists"];


    public const REFRESH_TOKEN = [
        'slug' => 'refresh_token',
        'name' => 'Refresh Token',
        'description' => "Refresh token.\n"
            . "Please refer to the ReadMe file to create a refresh token."];
//    public const SERVER_URI = 'server_uri';
    public const SERVER_URI = [
        'slug' => 'server_uri',
        'name' => 'Authorized Account URI',
        'description' => "URI Oauth Authentication Endpoint for server-based Zoho applications\n"
            . "for example https://accounts.zoho.eu or https://accounts.zoho.com"];
//    public const CLIENT_SECRET = 'client_secret';
    public const CLIENT_SECRET = [
        'slug' => 'client_secret',
        'name' => 'Client Secret',
        'description' => "Unique key generated when you register your application with Zoho.\n"
            . "This must be kept confidential.\n"
            . "E.g. a12345bC67e8fG9a12345bC67e8fG9a12345bC67e8fG9"];
//    public const CLIENT_ID = 'client_id';
    public const CLIENT_ID = [
        'slug' => 'client_id',
        'name' => 'Client ID',
        'description' => "Unique identifier you receive when you register your application with Zoho.\n"
            . "E.g. a1234b5c-1234-abcd-efgh-a1234b5cdef"];
    public const SETTINGS_NAME = "Simple ZMA LeadGen Settings";
    public const SETTINGS_SLUG = 'simplezmaleadgen_settings';
    public const FIRST_CONTRIBUTION = 'First Contribution';
    public const NEXT_CONTRIBUTION = 'Next Contribution';


    /**
     * @param $input
     * @param $preffix_log
     */
    public static function writeLog($input, $preffix_log = "Simple ZMA LeadGen Log")
    {
        try {
            if (self::getSaveLog()) {
                if (is_object($input)) {
                    $masquerade_input = (array)$input;
                } else {
                    $masquerade_input = $input;
                }
                if (is_array($masquerade_input)) {
                    $fields_to_hide = ['Signature'];
                    foreach ($fields_to_hide as $field_to_hide) {
                        unset($masquerade_input[$field_to_hide]);
                    }
                    Civi::log()->debug($preffix_log . "\n" . print_r($masquerade_input, TRUE));
                    return;
                }

                Civi::log()->debug($preffix_log . "\n" . $masquerade_input);
                return;
            }
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Simple ZMA LeadGen Configuration Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    /**
     * @return bool
     */
    public static function getSaveLog(): bool
    {
        $result = false;
        try {
            $result_ = self::getSettings(self::SAVE_LOG['slug']);
            if ($result_ == 1) {
                $result = true;
            }
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Simple ZMA LeadGen Log Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    /**
     * @return bool
     */
    public static function getSendContact(): bool
    {
        $result = false;
        try {
            $result_ = self::getSettings(self::SEND_CONTACT['slug']);
            if ($result_ == 1) {
                $result = true;
            }
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Send Contact Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    public static function getRefreshToken(): string
    {
        $result = "";
        try {
            $result = strval(self::getSettings(self::REFRESH_TOKEN['slug']));
//            self::writeLog($result, 'getValidateUEN');
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Write Log Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    public static function getClientID(): string
    {
        $result = "";
        try {
            $result = strval(self::getSettings(self::CLIENT_ID['slug']));
//            self::writeLog($result, 'getValidateUEN');
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    public static function getClientSecret(): string
    {
        $result = "";
        try {
            $result = strval(self::getSettings(self::CLIENT_SECRET['slug']));
//            self::writeLog($result, 'getValidateUEN');
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    public static function getServerURI(): string
    {
        $result = "";
        try {
            $result = strval(self::getSettings(self::SERVER_URI['slug']));
            $result = str_replace('https://', '', $result);
            $result = str_replace('http://', '', $result);
            $result = rtrim($result, '/');
//            self::writeLog($result, 'getValidateUEN');
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    public static function getBaseURI(): string
    {
        $serverUrl = self::getServerURI();
        $baseUrl = preg_replace('/^[^.]+\.(.*)$/', '$1', $serverUrl);
        $baseUrl = 'marketingautomation.' . $baseUrl;

        return $baseUrl; // Output: marketingautomation.zoho.eu
    }

    public static function getAccessTokenURL(): string
    {

        $refresh_token = self::getRefreshToken();
        $client_id = self::getClientID();
        $client_secret = self::getClientSecret();
        $redirect_uri = self::getServerURI();

        if ($refresh_token == "") return "";
        if ($client_id == "") return "";
        if ($client_secret == "") return "";
        if ($redirect_uri == "") return "";
        $result = "https://$redirect_uri/oauth/v2/token?"
            . "refresh_token=$refresh_token&"
            . "client_id=$client_id&"
            . "client_secret=$client_secret&"
//            . "redirect_uri=$redirect_uri&"
            . "grant_type=refresh_token";
//        self::writeLog($result, 'getstring');
        try {
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    /**
     * @return mixed
     */
    public static function getAccessToken()
    {
        if (self::$accessToken && self::$tokenExpiresAt > time()) {
            return self::$accessToken;
        }

        $url = self::getAccessTokenURL();
        $client = new GuzzleHttp\Client();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Guzzle';

        try {
            $response = $client->request('POST', $url, [
                'user_agent' => $user_agent,
                'headers' => [
                    'Accept' => 'text/plain',
                    'Content-Type' => 'application/*+json',
                    'X-VPS-Timeout' => '45',
                    'X-VPS-VIT-Integration-Product' => 'CiviCRM',
                    'X-VPS-Request-ID' => strval(rand(1, 1000000000)),
                ],
            ]);
            $decoded = json_decode($response->getBody(), true);
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            CRM_Core_Error::statusBounce('Error: Request error ', null, $e->getMessage());
            throw new CRM_Core_Exception('Error: Request error: ' . $e->getMessage());
        } catch (Exception $e) {
            CRM_Core_Error::statusBounce('Error: Another error: ', null, $e->getMessage());
            throw new CRM_Core_Exception('Error: Another error: ' . $e->getMessage());
        }
        self::$accessToken = $decoded['access_token'];
        self::$tokenExpiresAt = time() + ($decoded['expires_in'] - 100);
        return self::$accessToken;
    }

    public static function getLists()
    {
        $firstListName = self::FIRST_CONTRIBUTION;
        $nextListName = self::NEXT_CONTRIBUTION;

        // Get the list of mailing lists
        // Check if the First Contribution and Next Contribution lists exist
        $firstListExists = false;
        $nextListExists = false;
        $listOfDetails = self::getListOfDetails();
        foreach ($listOfDetails as $listDetail) {
            if ($listDetail['listname'] === $firstListName) {
                $firstListExists = true;
            }
            if ($listDetail['listname'] === $nextListName) {
                $nextListExists = true;
            }
        }
        $needNewList = false;
        // Create the First Contribution and Next Contribution lists if they do not exist
        if (!$firstListExists) {
            self::createMailingList($firstListName);
            $needNewList = true;
        }
        if (!$nextListExists) {
            self::createMailingList($nextListName);
            $needNewList = true;
        }

        // Get the listkeys of the First Contribution and Next Contribution lists
        $firstListKey = '';
        $nextListKey = '';
        if ($needNewList) {
            $listOfDetails = self::getListOfDetails();
        }
        foreach ($listOfDetails as $listDetail) {
            if ($listDetail['listname'] === $firstListName) {
                $firstListKey = $listDetail['listkey'];
            }
            if ($listDetail['listname'] === $nextListName) {
                $nextListKey = $listDetail['listkey'];
            }
        }

        return [
            'first_contribution_list_no' => $firstListKey,
            'next_contribution_list_no' => $nextListKey,
        ];
    }

    public static function createMailingList($listName)
    {
        $apiLink = 'addlistandleads';
        $params = [
            'resfmt' => 'JSON',
            'signupform' => 'public',
            'mode' => 'newlist',
            'listdescription' => $listName,
            'listname' => $listName,
        ];
        $response = self::getSomethingUsingGuzzlePost($apiLink, $params);
//        self::writeLog($response, 'simplezma');
        if ($response['status'] !== 'success') {
            throw new Exception('Failed to create mailing list');
        }
    }

    public static function getListOfDetails()
    {
        $apiLink = 'getmailinglists';
        $params = [
            'resfmt' => 'JSON',
        ];
        $response = self::getSomethingUsingGuzzlePost($apiLink, $params);
//        self::writeLog($response, 'simplezma getlist');
        return $response['list_of_details'] ?? [];
    }

    public static function getSomethingUsingGuzzlePost($apilink, $params)
    {
        $baseUrl = self::getBaseURI();
        $access_token = self::getAccessToken();

        $url = 'https://' . $baseUrl . '/api/v1/' . $apilink;
        $client = new GuzzleHttp\Client();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Guzzle';
//        self::writeLog($params, 'params');
//        self::writeLog($url, 'url');
        try {
            $response = $client->request('POST', $url, [
                'user_agent' => $user_agent,
                'headers' => [
                    'Accept' => 'text/plain',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'X-VPS-Timeout' => '45',
                    'X-VPS-VIT-Integration-Product' => 'CiviCRM',
                    'X-VPS-Request-ID' => strval(rand(1, 1000000000)),
                    'Authorization' => "Zoho-oauthtoken " . $access_token,
                ],
                'form_params' => $params,
            ]);
            $decoded = json_decode($response->getBody(), true);
//            self::writeLog($decoded, 'responseBody');
        } catch (GuzzleHttp\Exception\GuzzleException $e) {
            self::writeLog($e->getMessage(), 'Error: Request error ');
            CRM_Core_Error::statusBounce('Error: Request error ', null, $e->getMessage());
            throw new CRM_Core_Exception('Error: Request error: ' . $e->getMessage());
        } catch (Exception $e) {
            self::writeLog($e->getMessage(), 'Error: Request error ');
            CRM_Core_Error::statusBounce('Error: Another error: ', null, $e->getMessage());
            throw new CRM_Core_Exception('Error: Another error: ' . $e->getMessage());
        }
        return $decoded;
    }

    /**
     * for CLI etc
     * @param $contact_id
     * @return int|mixed
     */
    public static function sendContactByID($contact_id)
    {
        $zoho_id = 0;
        $contact = new CRM_Contact_DAO_Contact();
        $contact->id = $contact_id;
        if ($contact->find(TRUE)) {
            $zoho_id = self::sendContact($contact);
        }
        return $zoho_id;
    }

    /**
     * does not send contribution, rather sends contact of contribution as a lead
     * for CLI etc
     * @param $contact_id
     * @return int|mixed
     */
    public static function sendContributionContactByID($contribution_id)
    {
        $zoho_id = 0;
        $contribution = new CRM_Contribute_DAO_Contribution();
        $contribution->id = $contribution_id;
        if ($contribution->find(TRUE)) {
            $zoho_id = self::sendContributionContact($contribution);
        }
        return $zoho_id;
    }

    /**
     * @return mixed
     */
    public static function sendContact(\CRM_Contact_DAO_Contact $dnscontact)
    {
        $contact_id = intval($dnscontact->id);
        $contactCountributionsCount = self::getContributionCount($contact_id);
        $leadLists = self::getLists();

        $first_list = $leadLists['first_contribution_list_no'];
        $next_list = $leadLists['next_contribution_list_no'];
        if($contactCountributionsCount <= 1){
            $list = $first_list;
        }
        if($contactCountributionsCount > 1){
            $list = $next_list;
        }
        $first_name = $last_name = $phone = $email = "";


        $email = self::getPrimaryEmail($contact_id);
        if (!$email) {
            return 0;
        }
        $phone = self::getPrimaryPhone($contact_id);

        $first_name = $dnscontact->first_name;
        $legal_name = $dnscontact->legal_name;
        $last_name = $dnscontact->last_name;
        //Contact Email
        //Company Name
        if ($first_name) {
            $lead["First Name"] = $first_name;
        }else{
            $lead["First Name"] = 'Not Shown';
        }
        if ($last_name) {
            $lead["Last Name"] = $last_name;
        }else{
            $lead["Last Name"] = 'Not Shown';
        }
        if ($legal_name) {
            $lead["Company Name"] = $legal_name;
        }
        if ($phone) {
            $lead["Phone"] = $phone;
        }
        $lead["Contact Email"] = $email;
        $lead["Lead Email"] = $email;
        $apiLink = "json/listsubscribe";
        $params = [
            'listkey' => $list,
            'leadinfo' => json_encode($lead)
        ];
        $response = self::getSomethingUsingGuzzlePost($apiLink, $params);
        if(intval($response['code']) != 0){
            self::writeLog($response['message'], 'Zoho MA Error 0');
        }
        return intval($response['code']);
    }

    /**
     * @param $name
     * @return int|string|null
     */
    public static function getContributionStatusID($name)
    {
        return CRM_Utils_Array::key($name, \CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name'));
    }

    /**
     * @param $name
     * @return int|string|null
     */
    public static function getContributionStatusName($contributionStatusID)
    {
        return CRM_Core_PseudoConstant::getName('CRM_Contribute_BAO_Contribution', 'contribution_status_id', $contributionStatusID);
    }


    public static function getContributionCount($contact_id) {
        $count = civicrm_api4('Contribution', 'get', [
            'select' => [
                'row_count',
            ],
            'where' => [
                ['contribution_status_id', '=', 1],
                ['contact_id', '=', $contact_id],
            ],
            'limit' => 0,
            'checkPermissions' => FALSE,
        ])->count();
        return $count;
    }


    public static function sendContributionContact(\CRM_Contribute_DAO_Contribution $dnscontribution)
    {
        $contribution_id = intval($dnscontribution->id);
        $contribution_status_id = $dnscontribution->contribution_status_id;
        $contribution_status = self::getContributionStatusName($contribution_status_id);
        if ($contribution_status != 'Completed') {
//            self::writeLog('Not completed', "contribution_status");
//            self::writeLog($dnscontribution, 'dnscontribution');
            return;
        }
        $contact_id = $dnscontribution->contact_id;
        self::sendContactByID($contact_id);
    }

    /**
     * @param string $error_message
     * @param string $error_title
     */
    public static function showErrorMessage(string $error_message, string $error_title): void
    {
        $session = CRM_Core_Session::singleton();
        $userContext = $session->readUserContext();
        CRM_Core_Session::setStatus($error_message, $error_title, 'error');
        CRM_Utils_System::redirect($userContext);
    }



    /**
     * @return mixed
     */
    protected static function getSettings($setting = null)
    {
        $simple_settings = CRM_Core_BAO_Setting::getItem(self::SETTINGS_NAME, self::SETTINGS_SLUG);
        if ($setting === null) {
            if (is_array($simple_settings)) {
                return $simple_settings;
            }
            $simple_settings = [];
            return $simple_settings;
        }
        if ($setting) {
            $return_setting = CRM_utils_array::value($setting, $simple_settings);
            if (!$return_setting) {
                return false;
            }
            return $return_setting;
        }
    }

    /**
     * Check if a custom field exists given only the `label`, but we want to
     * check `name` first, then fall back to `label`.
     * Being a little bit paranoid but it's not clear if it's possible that a
     * really old install might have had the `name` generated differently than
     * the way core currently does it, since we never used to set the `name`
     * ourselves, so that's why we fall back to `label`.
     *
     * @param int $custom_group_id
     * @param string $field_label
     * @return bool
     */
    static function _custom_field_exists($custom_group_id, $field_label)
    {
        $params = array(
            'custom_group_id' => $custom_group_id,
            'name' => CRM_Utils_String::munge($field_label, '_', 64),
            'label' => $field_label,
            'options' => array('or' => array(array('name', 'label'))),
            'version' => 3,
        );
        $result = civicrm_api('custom_field', 'get', $params);
        return ($result['count'] != 0);
    }

    /**
     * @param $field_label
     * @return bool|int
     */
    static function get_custom_field_id($field_label)
    {
        $params = array(
//            'custom_group_id' => $custom_group_id,
            'name' => CRM_Utils_String::munge($field_label, '_', 64),
            'label' => $field_label,
            'options' => ['or' => [["name", "label"]]],
//            'version' => 3,
        );
        $result = civicrm_api3('CustomField', 'get', $params);
//        $result['count'] = 0;
        if ($result['count'] != 0) {
            foreach ($result['values'] as $id => $detail) {
                $custom_field_id = $id;
//                $custom_group_id = $detail['custom_group_id'];
            }
            return intval($custom_field_id);
        }
        return intval($result['count'] != 0);
    }

    /**
     * @param $title
     * @param $extends
     * @return int|string
     */
    public static function get_custom_group_id($title, $extends)
    {
        $custom_group_id = 0;
        $params = array(
            'title' => $title,
            'version' => 3,
        );

        require_once 'api/api.php';
        $result = civicrm_api('custom_group', 'get', $params);

        if ($result['count'] == 0) {
            $group = array(
                'title' => $title,
                'extends' => $extends,
                'collapse_display' => 0,
                'style' => 'Inline',
                'is_active' => 1,
                'version' => 3
            );
            $result = civicrm_api('custom_group', 'create', $group);
        }
        foreach ($result['values'] as $id => $detail) {
            $custom_group_id = $id;
        }
        return intval($custom_group_id);
    }

    public static function getPrimaryEmail($contactID)
    {
        // fetch the primary email
        $query = "
   SELECT civicrm_email.email as email
     FROM civicrm_contact
LEFT JOIN civicrm_email    ON ( civicrm_contact.id = civicrm_email.contact_id )
    WHERE civicrm_email.is_primary = 1
      AND civicrm_contact.id = %1";
        $p = [1 => [$contactID, 'Integer']];
        $dao = CRM_Core_DAO::executeQuery($query, $p);

        $email = NULL;
        if ($dao->fetch()) {
            $email = $dao->email;
        }
        return $email;
    }

    public static function getPrimaryPhone($contactID)
    {
        // fetch the primary phone
        $query = "
   SELECT civicrm_phone.phone as phone
     FROM civicrm_contact
LEFT JOIN civicrm_phone   ON ( civicrm_contact.id = civicrm_phone.contact_id )
    WHERE civicrm_phone.is_primary = 1
      AND civicrm_contact.id = %1";
        $p = [1 => [$contactID, 'Integer']];
        $dao = CRM_Core_DAO::executeQuery($query, $p);

        $phone = NULL;
        if ($dao->fetch()) {
            $phone = $dao->phone;
        }
        return $phone;
    }


}