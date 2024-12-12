<?php

use CRM_Simplezmaleadgen_ExtensionUtil as E;

require_once 'CustomFieldValues.php';

class CRM_Simplezmaleadgen_Utils
{
    private static $accessToken;
    private static $tokenExpiresAt;

    //    public const SAVE_LOG = 'save_log';
    public const SAVE_LOG = [
        'slug' => 'save_log',
        'name' => 'Save Log',
        'description' => "Write debugging output to CiviCRM log file"
    ];

    //    public const SEND_CONTACT = 'send_contact';
    public const SEND_CONTACT = [
        'slug' => 'send_contact',
        'name' => 'Send Contribution Contact to Zoho',
        'description' => "Send contribution contacts as leads to Zoho Marketing Automation.\n"
            . "Only for contributions marked as completed\n"
            . "and having contributor contact email\n"
            . "the contact will be sent to Zoho MA as leads.\n"
            . "New Contacts and Present Contacts will be added\n"
            . "to the corresponding lists"
    ];


    public const REFRESH_TOKEN = [
        'slug' => 'refresh_token',
        'name' => 'Refresh Token',
        'description' => "Refresh token.\n"
            . "Please refer to the ReadMe file to create a refresh token."
    ];
    //    public const SERVER_URI = 'server_uri';
    public const SERVER_URI = [
        'slug' => 'server_uri',
        'name' => 'Authorized Account URI',
        'description' => "URI Oauth Authentication Endpoint for server-based Zoho applications\n"
            . "for example https://accounts.zoho.eu or https://accounts.zoho.com"
    ];
    //    public const CLIENT_SECRET = 'client_secret';
    public const CLIENT_SECRET = [
        'slug' => 'client_secret',
        'name' => 'Client Secret',
        'description' => "Unique key generated when you register your application with Zoho.\n"
            . "This must be kept confidential.\n"
            . "E.g. a12345bC67e8fG9a12345bC67e8fG9a12345bC67e8fG9"
    ];
    //    public const CLIENT_ID = 'client_id';
    public const CLIENT_ID = [
        'slug' => 'client_id',
        'name' => 'Client ID',
        'description' => "Unique identifier you receive when you register your application with Zoho.\n"
            . "E.g. a1234b5c-1234-abcd-efgh-a1234b5cdef"
    ];

    public const MAILING_LIST_NAME = [
        'slug' => 'mailing_list_name',
        'name' => 'Mailing List Name',
        'description' => "Name of your mailing subscription list you want to add leads into.\n"
            . "E.g. Mailing Subscription"
    ];

    public const ACTIVITY_TYPE = [
        'slug' => 'activity_type',
        'name' => 'Activity Type',
        'description' => "Select the activity type linked to the donation form."
    ];

    public const CUSTOM_GROUP = [
        'slug' => 'custom_group',
        'name' => 'Custom Group',
        'description' => "Select the custom group linked to the donation form."
    ];

    public const CUSTOM_FIELD = [
        'slug' => 'custom_field',
        'name' => 'Custom Field',
        'description' => "Select the marketing automation checkbox trigger custom field\nlinked to the donation form.\nNOTE*: This will overwrite the previously saved custom field(s)"
    ];

    public const SETTINGS_NAME = "Simple ZMA LeadGen Settings";
    public const SETTINGS_SLUG = 'simplezmaleadgen_settings';
    public const FIRST_CONTRIBUTION = 'First-Time Donors';
    public const NEXT_CONTRIBUTION = 'Repeated Donors';

    public static function getMailingListName(): string
    {
        $result = "";
        try {
            $result = strval(self::getSettings(self::MAILING_LIST_NAME['slug']));
            // self::writeLog($result, "getMailingListName");
            //            self::writeLog($result, 'getValidateUEN');
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    private static $mailingList;
    public static function getMailingList()
    {
        if (self::$mailingList === null) {
            self::$mailingList = self::getMailingListName();
        }
        return self::$mailingList;
    }

    public static function getActivityType(): string
    {
        $result = "";
        try {
            $result = strval(self::getSettings(self::ACTIVITY_TYPE['slug']));
            // self::writeLog($result, "getActivityType");
            //            self::writeLog($result, 'getValidateUEN');
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    private static $activitypeId;
    public static function getActivityTypeId()
    {
        if (self::$activitypeId === null) {
            self::$activitypeId = self::getActivityType();
        }
        return self::$activitypeId;
    }

    public static function getCustomGroup(): string
    {
        $result = "";
        try {
            $result = strval(self::getSettings(self::CUSTOM_GROUP['slug']));
            self::writeLog($result, "getCustomGroup");
            //            self::writeLog($result, 'getValidateUEN');
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    private static $customGroupId;
    public static function getCustomGroupId()
    {
        if (self::$customGroupId === null) {
            self::$customGroupId = self::getCustomGroup();
        }
        return self::$customGroupId;
    }

    public static function getCustomField(): string
    {
        $result = "";
        try {
            $result = strval(self::getSettings(self::CUSTOM_FIELD['slug']));
            self::writeLog($result, "getCustomFields");

            //            self::writeLog($result, 'getValidateUEN');
            return $result;
        } catch (\Exception $exception) {
            $error_message = $exception->getMessage();
            $error_title = 'Config Required';
            self::showErrorMessage($error_message, $error_title);
        }
    }

    private static $customFieldIds;
    public static function getCustomFieldId()
    {
        if (self::$customFieldIds === null) {
            self::$customFieldIds = self::getCustomField();
        }
        return self::$customFieldIds;
    }

    /**
     * @param $input
     * @param $preffix_log
     */
    public static function writeLog($input, $preffix_log = "Simple ZMA LeadGen Log")
    {
        try {
            if (self::getSaveLog()) {
                if (is_object($input)) {
                    $masquerade_input = (array) $input;
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

        if ($refresh_token == "")
            return "";
        if ($client_id == "")
            return "";
        if ($client_secret == "")
            return "";
        if ($redirect_uri == "")
            return "";
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
        // hardcoded default lists
        $firstListName = self::FIRST_CONTRIBUTION;
        $nextListName = self::NEXT_CONTRIBUTION;

        // dynamic list name, can change in config page
        $mailingListName = self::getMailingList();
        // self::writeLog($mailingListName, "list name");

        // Check if the lists exist
        $firstListExists = false;
        $nextListExists = false;
        $mailingListExists = false;
        $listOfDetails = self::getListOfDetails();
        foreach ($listOfDetails as $listDetail) {
            if ($listDetail['listname'] === $firstListName) {
                $firstListExists = true;
            }
            if ($listDetail['listname'] === $nextListName) {
                $nextListExists = true;
            }
            if ($listDetail['listname'] == $mailingListName) {
                $mailingListExists = true;
            }
        }
        $needNewList = false;
        // Create the lists if they do not exist
        if (!$firstListExists) {
            self::createMailingList($firstListName);
            $needNewList = true;
        }
        if (!$nextListExists) {
            self::createMailingList($nextListName);
            $needNewList = true;
        }
        if (!$mailingListExists) {
            self::createMailingList($mailingListName);
            $needNewList = true;
        }

        // Get the listkeys of the lists
        $firstListKey = '';
        $nextListKey = '';
        $mailingListKey = '';
        if ($needNewList) {
            $listOfDetails = self::getListOfDetails();
            // self::writeLog($listOfDetails, "listofdetails");
        }
        foreach ($listOfDetails as $listDetail) {
            if ($listDetail['listname'] === $firstListName) {
                $firstListKey = $listDetail['listkey'];
            }
            if ($listDetail['listname'] === $nextListName) {
                $nextListKey = $listDetail['listkey'];
            }
            if ($listDetail['listname'] === $mailingListName) {
                $mailingListKey = $listDetail['listkey'];
            }
        }
        // self::writeLog(["first" => $firstListKey, "next" => $nextListKey, "mailing" => $mailingListKey]);


        return [
            'first_contribution_list_no' => $firstListKey,
            'next_contribution_list_no' => $nextListKey,
            self::MAILING_LIST_NAME['slug'] . "_list_no" => $mailingListKey,
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
        $response = self::getSomethingUsingGuzzlePost($apiLink, $params, 'POST');
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
        $response = self::getSomethingUsingGuzzlePost($apiLink, $params, 'GET');
        //        self::writeLog($response, 'simplezma getlist');
        return $response['list_of_details'] ?? [];
    }

    public static function createCustomField($field, $fieldType)
    {
        // self::writeLog($field, "field");
        // check if field already exists
        $exists = false;

        $apiLinkCheck = 'lead/allfields';
        $params = [
            'type' => 'json'
        ];
        $responseCheck = self::getSomethingUsingGuzzlePost($apiLinkCheck, $params, 'GET');
        $fieldname = $responseCheck['response']['fieldnames']['fieldname'] ?? [];
        // self::writeLog($responseCheck, "response check");
        // self::writeLog($fieldname, "fieldname");

        if (!empty($fieldname)) {
            foreach ($fieldname as $fieldn) {
                if ($fieldn["DISPLAY_NAME"] === $field) {
                    // self::writeLog($fieldn["DISPLAY_NAME"], "display name");
                    $exists = true;
                }
            }
        }

        if (!$exists) {
            $apiLinkCreate = 'custom/add';
            $params = [
                'type' => 'json',
                'fieldname' => $field,
                'fieldtype' => $fieldType,
            ];
            self::getSomethingUsingGuzzlePost($apiLinkCreate, $params, 'POST');
        }
    }

    public static function getSomethingUsingGuzzlePost($apilink, $params, $method)
    {
        $baseUrl = self::getBaseURI();
        $access_token = self::getAccessToken();

        $url = 'https://' . $baseUrl . '/api/v1/' . $apilink;
        $client = new GuzzleHttp\Client();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Guzzle';
        self::writeLog($params, 'params');
        //        self::writeLog($url, 'url');
        try {
            $response = $client->request($method, $url, [
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
            self::writeLog($decoded, 'responseBody');
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
    public static function sendContactByID($contact_id, $contribution_id)
    {
        $zoho_id = 0;
        $contact = new CRM_Contact_DAO_Contact();
        $contact->id = $contact_id;
        if ($contact->find(TRUE)) {
            $zoho_id = self::sendContact($contact, $contribution_id);
        }
        return $zoho_id;
    }


    // /**
    //  * does not send contribution, rather sends contact of contribution as a lead
    //  * for CLI etc
    //  * @param $contact_id
    //  * @return int|mixed
    //  */
    // public static function sendContributionContactByID($contribution_id)
    // {
    //     $zoho_id = 0;
    //     $contribution = new CRM_Contribute_DAO_Contribution();
    //     $contribution->id = $contribution_id;
    //     if ($contribution->find(TRUE)) {
    //         $zoho_id = self::sendContributionContact($contribution);
    //     }
    //     return $zoho_id;
    // }

    public static function getContributionDetails($contact_id, $contribution_id)
    {
        self::writeLog($contact_id, 'contact id');
        self::writeLog($contribution_id, 'contribution id');

        $contribution = civicrm_api4('Contribution', 'get', [
            'where' => [
                ['contact_id', '=', $contact_id],
                ['id', '=', $contribution_id],
                [
                    'OR',
                    [
                        ['financial_type_id:name', '=', 'Donation'],
                        ['financial_type_id:name', '=', 'Tax Deductible Donation'],
                        ['financial_type_id:name', '=', 'Non-Tax Deductible Donation']
                    ]
                ],
            ],
            'checkPermissions' => FALSE,
        ]);

        self::writeLog($contribution, 'contribution');

        $details = array(
            "donation_method" => $contribution[0]["payment_instrument_id"],
            "donation_amount" => $contribution[0]["total_amount"],
        );

        return $details;
    }

    public static function getTotalAmountDonatedContribution($contact_id)
    {
        $contributions = civicrm_api4('Contribution', 'get', [
            'where' => [
                ['contact_id', '=', $contact_id],
                [
                    'OR',
                    [
                        ['financial_type_id:name', '=', 'Donation'],
                        ['financial_type_id:name', '=', 'Tax Deductible Donation'],
                        ['financial_type_id:name', '=', 'Non-Tax Deductible Donation']
                    ]
                ],
            ],
            'checkPermissions' => FALSE,
        ]);

        $totalAmount = 0;
        foreach ($contributions as $contribution) {
            $totalAmount += $contribution["total_amount"];
        }

        // self::writeLog($totalAmount, "total amount contribution");
        return $totalAmount;
    }

    /**
     * @return mixed
     */

    // should add lead into first & repeated donors after form activity is completed & contribution created
    public static function sendContact(\CRM_Contact_DAO_Contact $dnscontact, $contribution_id)
    {
        $contact_id = intval($dnscontact->id);

        $contributionDetails = self::getContributionDetails($contact_id, $contribution_id);
        self::writeLog($contributionDetails);

        $savedCustomFieldId = self::getCustomFieldId();
        self::writeLog($savedCustomFieldId);
        $getCustomFieldLabel = self::getCustomFieldLabelAndGroupName($savedCustomFieldId);


        $customFields = civicrm_api4('CustomField', 'get', [
            'select' => [
                'custom_group_id:name',
                'name',
            ],
            'where' => [
                ['label', '=', $getCustomFieldLabel["label"]],
                ['custom_group_id.extends', '=', 'Contribution'],
            ],
            'checkPermissions' => FALSE,
        ]);
        self::writeLog($customFields, 'custom fields');

        $customFieldWithGroup = $customFields[0]["custom_group_id:name"] . '.' . $customFields[0]["name"];

        $contribution = civicrm_api4('Contribution', 'get', [
            'select' => [
                $customFieldWithGroup,
            ],
            'where' => [
                ['contact_id', '=', $contact_id],
                ['id', '=', $contribution_id],
                [
                    'OR',
                    [
                        ['financial_type_id:name', '=', 'Donation'],
                        ['financial_type_id:name', '=', 'Tax Deductible Donation'],
                        ['financial_type_id:name', '=', 'Non-Tax Deductible Donation']
                    ]
                ],
            ],
            'checkPermissions' => FALSE,
        ]);
        self::writeLog($contribution, "contribution marketing consent choice");

        if ($contribution[0][$customFieldWithGroup][0] == 1) {
            $donationMethod = self::getMethod($contributionDetails["donation_method"]);
            self::writeLog($donationMethod, 'donation method');

            // switch ($contributionDetails["donation_method"]) {
            //     case 1:
            //         $donationMethod = "Credit Card";
            //         break;
            //     case 2:
            //         $donationMethod = "Debit Card";
            //         break;
            //     case 3:
            //         $donationMethod = "Cash";
            //         break;
            //     case 4:
            //         $donationMethod = "Cheque";
            //         break;
            //     case 5:
            //         $donationMethod = "EFT";
            //         break;
            //     default:
            //         $donationMethod = "";
            //         break;
            // }
            $donationAmount = $contributionDetails["donation_amount"];

            $leadLists = self::getLists();

            $subList = $leadLists[self::MAILING_LIST_NAME['slug'] . "_list_no"];
            $first_list = $leadLists['first_contribution_list_no'];
            $next_list = $leadLists['next_contribution_list_no'];

            $activityTypeId = self::getActivityTypeId();
            $contributionCount = self::getContributionCount($contact_id);
            $activityCount = self::getActivityCount($contact_id, $activityTypeId);

            $donationCount = $contributionCount/* + $activityCount*/ ;

            $first_name = $last_name = $phone = $email = $birth_date = "";
            $first_name = $dnscontact->first_name;
            $last_name = $dnscontact->last_name;
            $birth_date = $dnscontact->birth_date;
            $formatted_birth_date = date('M d, Y', strtotime($birth_date));
            $email = self::getPrimaryEmail($contact_id);
            $phone = self::getPrimaryPhone($contact_id);
            $addressInfo = self::getAddressInfo($contact_id);

            // create custom fields
            $fieldBirthDate = "Birth Date";
            $fieldFirstDonationMethod = "First Donation Method";
            $fieldFirstDonationAmount = "First Donation Amount";
            $fieldSecondDonationMethod = "Second Donation Method";
            $fieldSecondDonationAmount = "Second Donation Amount";
            $fieldTotalAmountDonated = "Total Amount Donated";
            $fieldTotalDonationsMade = "Total Donations Made";

            self::createCustomField($fieldBirthDate, 'Text');
            self::createCustomField($fieldFirstDonationMethod, 'Text');
            self::createCustomField($fieldFirstDonationAmount, 'Integer');
            self::createCustomField($fieldSecondDonationMethod, 'Text');
            self::createCustomField($fieldSecondDonationAmount, 'Integer');
            self::createCustomField($fieldTotalAmountDonated, 'Integer');
            self::createCustomField($fieldTotalDonationsMade, 'Integer');

            if ($first_name) {
                $lead["First Name"] = $first_name;
            } else {
                $lead["First Name"] = '';
            }
            if ($last_name) {
                $lead["Last Name"] = $last_name;
            } else {
                $lead["Last Name"] = '';
            }
            if ($email) {
                $lead["Contact Email"] = $email;
            }
            if ($phone) {
                $lead["Phone"] = $phone;
            }
            if ($formatted_birth_date) {
                $lead[$fieldBirthDate] = $formatted_birth_date;
            }
            if ($addressInfo["street_address"]) {
                $lead["Address"] = $addressInfo["street_address"];
            }
            if ($addressInfo["city"]) {
                $lead["City"] = $addressInfo["city"];
            }
            if ($addressInfo["country"]) {
                $lead["Country"] = $addressInfo["country"];
            }
            if ($addressInfo["postal_code"]) {
                $lead["Zip Code"] = $addressInfo["postal_code"];
            }

            $apiLinkSubscribe = "json/listsubscribe";
            $params = [
                'listkey' => $subList,
                'leadinfo' => json_encode($lead),
            ];

            $response = self::getSomethingUsingGuzzlePost($apiLinkSubscribe, $params, 'POST');
            if (intval($response['code']) != 0) {
                self::writeLog($response['message'], 'Zoho MA Error 0');
            }

            $totalAmountDonatedContribution = self::getTotalAmountDonatedContribution($contact_id);
            // $totalAmountDonatedActivity = self::getTotalAmountDonatedActivity($contact_id, $activityTypeId);

            $totalAmountDonated = /*$totalAmountDonatedActivity +*/ $totalAmountDonatedContribution;

            if ($donationCount == 1) {
                $list = $first_list;
                if ($donationMethod) {
                    $lead[$fieldFirstDonationMethod] = $donationMethod;
                }
                if ($donationAmount) {
                    $lead[$fieldFirstDonationAmount] = $donationAmount;
                }
                if ($totalAmountDonated) {
                    $lead[$fieldTotalAmountDonated] = $totalAmountDonated;
                }
                if ($donationCount) {
                    $lead[$fieldTotalDonationsMade] = $donationCount;
                }
            } elseif ($donationCount == 2) {
                // first unsubscribe from first-time donors
                $apiLinkUnsubscribe = 'json/listunsubscribe';
                $params = ['listkey' => $first_list, 'leadinfo' => json_encode($lead)];

                $response = self::getSomethingUsingGuzzlePost($apiLinkUnsubscribe, $params, 'POST');
                if (intval($response['code']) != 0) {
                    self::writeLog($response['message'], 'Zoho MA Error 0');
                }

                // then subscribe to repeated donors
                $list = $next_list;
                if ($donationMethod) {
                    $lead[$fieldSecondDonationMethod] = $donationMethod;
                }
                if ($donationAmount) {
                    $lead[$fieldSecondDonationAmount] = $donationAmount;
                }
                if ($totalAmountDonated) {
                    $lead[$fieldTotalAmountDonated] = $totalAmountDonated;
                }
                if ($donationCount) {
                    $lead[$fieldTotalDonationsMade] = $donationCount;
                }
            } elseif ($donationCount > 2) {
                // first unsubscribe from first-time donors
                $apiLinkUnsubscribe = 'json/listunsubscribe';
                $params = ['listkey' => $first_list, 'leadinfo' => json_encode($lead)];

                $response = self::getSomethingUsingGuzzlePost($apiLinkUnsubscribe, $params, 'POST');
                if (intval($response['code']) != 0) {
                    self::writeLog($response['message'], 'Zoho MA Error 0');
                }

                $list = $next_list;
                if ($totalAmountDonated) {
                    $lead[$fieldTotalAmountDonated] = $totalAmountDonated;
                }
                if ($donationCount) {
                    $lead[$fieldTotalDonationsMade] = $donationCount;
                }
            }

            $params = [
                'listkey' => $list,
                'leadinfo' => json_encode($lead),
            ];

            $response = self::getSomethingUsingGuzzlePost($apiLinkSubscribe, $params, 'POST');
            if (intval($response['code']) != 0) {
                self::writeLog($response['message'], 'Zoho MA Error 0');
            }

            return intval($response['code']);
        }
        self::writeLog("marketing consent unchecked");
    }

    // /**
    //  * @param $name
    //  * @return int|string|null
    //  */
    // public static function getContributionStatusID($name)
    // {
    //     return CRM_Utils_Array::key($name, \CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name'));
    // }

    /**
     * @param $name
     * @return int|string|null
     */
    public static function getContributionStatusName($contributionStatusID)
    {
        return CRM_Core_PseudoConstant::getName('CRM_Contribute_BAO_Contribution', 'contribution_status_id', $contributionStatusID);
    }

    public static function sendContributionContact(\CRM_Contribute_DAO_Contribution $dnscontribution)
    {
        $contribution_id = intval($dnscontribution->id);
        $contribution_status_id = $dnscontribution->contribution_status_id;
        $contribution_status = self::getContributionStatusName($contribution_status_id);
        $contact_id = $dnscontribution->contact_id;
        $contribution_financial = $dnscontribution->financial_type_id;

        // Get the financial type ID by its name
        $donationFinancialTypeId = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'financial_type_id', 'Donation');
        // self::writeLog($donationFinancialTypeId, 'donationFinancialTypeId');

        $taxDeductibleDonationFinancialTypeId = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'financial_type_id', 'Tax Deductible Donation');
        // self::writeLog($taxDeductibleDonationFinancialTypeId, 'taxDeductibleDonationFinancialTypeId');

        $nonTaxDeductibleDonationFinancialTypeId = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'financial_type_id', 'Non-Tax Deductible Donation');
        // self::writeLog($nonTaxDeductibleDonationFinancialTypeId, 'nonTaxDeductibleDonationFinancialTypeId');

        if ($contribution_status != 'Completed') {
            // self::writeLog('Not completed', "contribution_status");
            // self::writeLog($dnscontribution, 'dnscontribution');
            return;
        }
        if (
            $contribution_financial == $donationFinancialTypeId ||
            $contribution_financial == $taxDeductibleDonationFinancialTypeId ||
            $contribution_financial == $nonTaxDeductibleDonationFinancialTypeId
        ) {
            self::sendContactByID($contact_id, $contribution_id);
            // self::writeLog('sent contact by id');
        } else {
            self::writeLog('Wrong financial type', "contribution_financial");
        }
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
        // Log or print out $simple_settings for debugging
        error_log('Simple Settings: ' . print_r($simple_settings, true));

        if ($setting === null) {
            if (is_array($simple_settings)) {
                return $simple_settings;
            }
            $simple_settings = [];
            return $simple_settings;
        }
        if ($setting) {
            $return_setting = CRM_utils_array::value($setting, $simple_settings);
            // Log or print out $return_setting for debugging
            error_log('Return Setting: ' . print_r($return_setting, true));

            if (!$return_setting) {
                return false;
            }
            return $return_setting;
        }
    }

    public static function getActivityCount($contact_id, $activityTypeId)
    {
        $query = "SELECT COUNT(DISTINCT(activity_id)) as count
                    FROM civicrm_activity_contact ac, civicrm_activity a 
                    WHERE ac.activity_id = a.id 
                    AND ac.contact_id =  %1
                    AND a.activity_type_id = %2";
        $p = [
            1 => [$contact_id, 'Integer'],
            2 => [$activityTypeId, 'Integer']
        ];
        $dao = CRM_CORE_DAO::executeQuery($query, $p);

        $count = 0;
        if ($dao->fetch()) {
            $count = $dao->count;
        }
        return $count;
    }

    public static function getContributionCount($contact_id)
    {
        $query = "SELECT COUNT(*) as count
                    FROM civicrm_contribution
                    INNER JOIN civicrm_financial_type ON civicrm_contribution.financial_type_id = civicrm_financial_type.id
                    WHERE contact_id = %1
                    AND civicrm_financial_type.name IN ('Donation', 'Tax Deductible Donation', 'Non-Tax Deductible Donation')";
        $p = [1 => [$contact_id, 'Integer'],];
        $dao = CRM_CORE_DAO::executeQuery($query, $p);

        $count = 0;
        if ($dao->fetch()) {
            $count = $dao->count;
        }
        return $count;
    }

    public static function getActivityTypeOptions()
    {
        $activity_types = civicrm_api4('OptionValue', 'get', [
            'where' => [
                ['option_group_id', '=', 2],
            ],
            'checkPermissions' => FALSE,
        ]);

        $options = [];

        if (!empty($activity_types)) {
            $options[''] = E::ts("- Please Select"); // default option if nothing is selected
            foreach ($activity_types as $activity_type) {
                $options[$activity_type['value']] = E::ts($activity_type['label']);
            }
        }

        return $options;
    }

    public static function getCustomGroupOptions()
    {
        $custom_groups = civicrm_api4('CustomGroup', 'get', [
            'checkPermissions' => TRUE,
        ]);

        $options = [];

        if (!empty($custom_groups)) {
            $options[''] = E::ts("- Please Select"); // default option if nothing is selected
            foreach ($custom_groups as $custom_group) {
                $options[$custom_group['id']] = E::ts($custom_group['title']);
            }
        }

        return $options;
    }

    public static function getCustomFieldsOptions($customGroupId)
    {
        $custom_fields = civicrm_api4('CustomField', 'get', [
            'where' => [
                ['custom_group_id', '=', $customGroupId],
            ],
            'checkPermissions' => FALSE,
        ]);

        $options = [];

        if (!empty($custom_fields)) {
            foreach ($custom_fields as $custom_field) {
                $options[$custom_field['id']] = E::ts($custom_field['label']);
            }
        }

        return $options;
    }

    public static function getCustomFieldLabelAndGroupName($customFieldId)
    {
        $customField = civicrm_api4('CustomField', 'get', [
            'select' => [
                'label',
                'custom_group_id:label',
            ],
            'where' => [
                ['id', '=', $customFieldId],
            ],
            'checkPermissions' => FALSE,
        ]);

        // self::writeLog($customField, 'getcustomgfieldlabel');
        if ($customField) {
            return $customField[0];
        }
    }

    // public static function getActivityTypeId($activityType)
    // {
    //     $query = "SELECT value
    //             FROM civicrm_option_value
    //             WHERE name = %1";
    //     $p = [1 => [$activityType, 'String']];
    //     $dao = CRM_Core_DAO::executeQuery($query, $p);

    //     $value = NULL;
    //     if ($dao->fetch()) {
    //         $value = $dao->value;
    //     }
    //     return $value;
    // }

    public static function getContactId($activityId)
    {
        $query = "SELECT contact_id
                 FROM civicrm_activity_contact
                 WHERE activity_id = %1
                 LIMIT 1";
        $p = [1 => [$activityId, 'Integer']];
        $dao = CRM_Core_DAO::executeQuery($query, $p);

        $contactId = NULL;
        if ($dao->fetch()) {
            $contactId = $dao->contact_id;
        }
        // self::writeLog($contactId, "inside getcontactid");
        return $contactId;
    }

    public static function getActivitiesByContact($contactId)
    {
        $query = "SELECT DISTINCT activity_id
        FROM civicrm_activity_contact
        WHERE contact_id = %1";
        $p = [1 => [$contactId, 'Integer']];
        $dao = CRM_Core_DAO::executeQuery($query, $p);

        $activities = [];
        while ($dao->fetch()) {
            $activities[] = $dao->activity_id;
        }
        // self::writeLog($activities);
        return $activities;
    }

    public static function getCustomGroupName($customGroupId)
    {
        $customGroupId = self::getCustomGroupId();

        $customGroupName = civicrm_api4('CustomGroup', 'get', [
            'select' => [
                'name',
            ],
            'where' => [
                ['id', '=', $customGroupId],
            ],
            'checkPermissions' => FALSE,
        ]);

        return $customGroupName[0]['name'];
    }

    public static function getCustomFieldsByCustomGroup($customGroupId)
    {
        $customFields = civicrm_api4('CustomField', 'get', [
            'where' => [
                ['custom_group_id', '=', $customGroupId],
            ],
            'checkPermissions' => FALSE,
        ]);

        self::writeLog($customFields);
        return $customFields;
    }
    public static function getActivityDetails($activityTypeId, $activityId)
    {
        // self::writeLog($activityTypeId, "activity type id inside getactivitydetails");
        // self::writeLog($activityId, "activity id inside getactivitydetails");

        // $query = "SELECT created_date, donation_method_9, donation_amount_10, subscribe_to_mailing_list_8
        //         FROM civicrm_activity a, civicrm_value_donation_form_4 d
        //         WHERE a.id = d.entity_id
        //         AND a.activity_type_id = %1
        //         AND a.id = %2";
        // $p = [
        //     1 => [$activityTypeId, 'Integer'],
        //     2 => [$activityId, 'Integer']
        // ];
        // $dao = CRM_Core_DAO::executeQuery($query, $p);

        // $details = [];
        // if ($dao->fetch()) {
        //     // $details["created_date"] = $dao->created_date;
        //     $details["donation_method"] = $dao->donation_method_9;
        //     $details["donation_amount"] = $dao->donation_amount_10;
        //     $details["subscribe_choice"] = $dao->subscribe_to_mailing_list_8;
        // }
        // return $details;

        $customGroupId = self::getCustomGroupId();
        self::writeLog($customGroupId, 'custom group id');
        $customGroupName = self::getCustomGroupName($customGroupId);
        self::writeLog($customGroupName, 'custom group name');
        $customFields = self::getCustomFieldsByCustomGroup($customGroupId);

        $savedCustomFieldId = self::getCustomFieldId();
        $getCustomFieldLabel = self::getCustomFieldLabelAndGroupName($savedCustomFieldId);

        self::writeLog($getCustomFieldLabel, 'get custom field label');

        $apiSelectFields = [];

        // check by saved custom fields (only "marketing consent" for now)
        foreach ($customFields as $customField) {
            // foreach ($savedCustomFieldIds as $savedFieldId) {
            if ($customField["label"] == $getCustomFieldLabel["label"]) {
                $customFieldName = $customField["name"];
                array_push($apiSelectFields, $customGroupName . '.' . $customFieldName);
            }
            // }
        }

        self::writeLog($apiSelectFields, 'apiSelectFields');

        $activity = civicrm_api4('Activity', 'get', [
            'select' => $apiSelectFields,
            'where' => [
                ['activity_type_id', '=', $activityTypeId],
                ['id', '=', $activityId],
            ],
            'checkPermissions' => FALSE,
        ]);
        self::writeLog($activity, 'activity retrieved');

        $customFieldValues = new CustomFieldValues();

        self::writeLog($activity[0][$customGroupName . '.' . $customFieldName], 'activity_subscribe');
        // self::writeLog($activity[0][$customGroupName . '.' . $method], 'activity_method');
        // self::writeLog($activity[0][$customGroupName . '.' . $amount], 'activity_amount');

        $customFieldValues->setSubscribeChoice($activity[0][$customGroupName . '.' . $customFieldName]);
        // $customFieldValues->setDonationMethod($activity[0][$customGroupName . '.' . $method]);
        // $customFieldValues->setDonationAmount($activity[0][$customGroupName . '.' . $amount]);

        return $customFieldValues;

        // $details = array(
        //     "subscribe_choice" => $activity[0][$customGroupName . '.' . $subscribe],
        //     "donation_method" => $activity[0][$customGroupName . '.' . $method],
        //     "donation_amount" => $activity[0][$customGroupName . '.' . $amount],
        // );

        // return $details;
    }

    public static function getTotalAmountDonatedActivity($contactId, $activityTypeId)
    {
        $activities = self::getActivitiesByContact($contactId);
        // self::writeLog($activities);

        $totalAmount = 0;
        foreach ($activities as $activity) {
            $getActivityDetails = self::getActivityDetails($activityTypeId, $activity);
            $totalAmount += $getActivityDetails->getDonationAmount();
        }

        return $totalAmount;
    }

    public static function getMethod($donationMethodId)
    {
        self::writeLog($donationMethodId, 'donation method id');
        // dynamically retrieves custom fields
        // $customGroupId = self::getCustomGroupId();
        // $customFields = self::getCustomFields($customGroupId);

        // check by custom field keywords, compulsory words to be named for custom field
        // foreach ($customFields as $customField) {
        //     if (/*str_contains($customField["label"], "Method")*/ $customField["label"] === "Payment Methods") {
        //         $donationMethod = $customField["name"];
        //     }
        // }
        // self::writeLog($donationMethod, 'donation method name');

        // get all payment methods
        $paymentMethods = civicrm_api4('OptionValue', 'get', [
            'select' => [
                'value',
                'label',
            ],
            'join' => [
                ['OptionGroup AS option_group', 'LEFT', ['option_group_id', '=', 'option_group.id']],
            ],
            'where' => [
                ['option_group.name', '=', 'payment_instrument'],
            ],
            'checkPermissions' => FALSE,
        ]);

        self::writeLog($paymentMethods, 'payment methods');

        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod["value"] == $donationMethodId) {
                return $paymentMethod["label"];
            }
        }
    }

    // should only add lead into mailing subscription list
    public static function sendActivityContact($contactId, $activityTypeId, $activityId)
    {
        $contact = new CRM_Contact_DAO_Contact();
        $contact->id = $contactId;

        $activityDetails = self::getActivityDetails($activityTypeId, $activityId);

        $donationMethodId = $activityDetails->getDonationMethod();

        $donationMethod = self::getMethod($donationMethodId);
        self::writeLog($donationMethod, 'donation method');

        // switch ($getDonationMethod) {
        //     case 1:
        //         $donationMethod = "Credit Card";
        //         break;
        //     case 2:
        //         $donationMethod = "Debit Card";
        //         break;
        //     case 3:
        //         $donationMethod = "Cash";
        //         break;
        //     case 4:
        //         $donationMethod = "Cheque";
        //         break;
        //     case 5:
        //         $donationMethod = "EFT";
        //         break;
        //     default:
        //         $donationMethod = "";
        //         break;
        // }
        $donationAmount = $activityDetails->getDonationAmount();

        if ($contact->find(TRUE)) { // if contact is found in db
            $leadLists = self::getLists();

            $subList = $leadLists[self::MAILING_LIST_NAME['slug'] . "_list_no"];
            $first_list = $leadLists['first_contribution_list_no'];
            $next_list = $leadLists['next_contribution_list_no'];

            $activityCount = self::getActivityCount($contactId, $activityTypeId);
            $contributionCount = self::getContributionCount($contactId);

            $donationCount = $activityCount + $contributionCount;

            $first_name = $last_name = $phone = $email = $birth_date = "";
            $first_name = $contact->first_name;
            $last_name = $contact->last_name;
            $birth_date = $contact->birth_date;
            $formatted_birth_date = date('M d, Y', strtotime($birth_date));
            $email = self::getPrimaryEmail($contactId);
            $phone = self::getPrimaryPhone($contactId);
            $addressInfo = self::getAddressInfo($contactId);

            // create custom fields
            $fieldBirthDate = "Birth Date";
            $fieldFirstDonationMethod = "First Donation Method";
            $fieldFirstDonationAmount = "First Donation Amount";
            $fieldSecondDonationMethod = "Second Donation Method";
            $fieldSecondDonationAmount = "Second Donation Amount";
            $fieldTotalAmountDonated = "Total Amount Donated";
            $fieldTotalDonationsMade = "Total Donations Made";

            self::createCustomField($fieldBirthDate, 'Text');
            self::createCustomField($fieldFirstDonationMethod, 'Text');
            self::createCustomField($fieldFirstDonationAmount, 'Integer');
            self::createCustomField($fieldSecondDonationMethod, 'Text');
            self::createCustomField($fieldSecondDonationAmount, 'Integer');
            self::createCustomField($fieldTotalAmountDonated, 'Integer');
            self::createCustomField($fieldTotalDonationsMade, 'Integer');

            if ($first_name) {
                $lead["First Name"] = $first_name;
            } else {
                $lead["First Name"] = '';
            }
            if ($last_name) {
                $lead["Last Name"] = $last_name;
            } else {
                $lead["Last Name"] = '';
            }
            if ($email) {
                $lead["Contact Email"] = $email;
            }
            if ($phone) {
                $lead["Phone"] = $phone;
            }
            if ($formatted_birth_date) {
                $lead[$fieldBirthDate] = $formatted_birth_date;
            }
            if ($addressInfo["street_address"]) {
                $lead["Address"] = $addressInfo["street_address"];
            }
            if ($addressInfo["city"]) {
                $lead["City"] = $addressInfo["city"];
            }
            if ($addressInfo["country"]) {
                $lead["Country"] = $addressInfo["country"];
            }
            if ($addressInfo["postal_code"]) {
                $lead["Zip Code"] = $addressInfo["postal_code"];
            }

            $apiLinkSubscribe = "json/listsubscribe";
            $params = [
                'listkey' => $subList,
                'leadinfo' => json_encode($lead),
            ];

            $response = self::getSomethingUsingGuzzlePost($apiLinkSubscribe, $params, 'POST');
            if (intval($response['code']) != 0) {
                self::writeLog($response['message'], 'Zoho MA Error 0');
            }

            // $totalAmountDonatedActivity = self::getTotalAmountDonatedActivity($contactId, $activityTypeId);
            // $totalAmountDonatedContribution = self::getTotalAmountDonatedContribution($contactId);

            // $totalAmountDonated = $totalAmountDonatedActivity + $totalAmountDonatedContribution;

            // if ($donationCount == 1) {
            //     $list = $first_list;
            //     if ($donationMethod) {
            //         $lead[$fieldFirstDonationMethod] = $donationMethod;
            //     }
            //     if ($donationAmount) {
            //         $lead[$fieldFirstDonationAmount] = $donationAmount;
            //     }
            //     if ($totalAmountDonated) {
            //         $lead[$fieldTotalAmountDonated] = $totalAmountDonated;
            //     }
            //     if ($donationCount) {
            //         $lead[$fieldTotalDonationsMade] = $donationCount;
            //     }
            // } elseif ($donationCount == 2) {
            //     // first unsubscribe from first-time donors
            //     $apiLinkUnsubscribe = 'json/listunsubscribe';
            //     $params = ['listkey' => $first_list, 'leadinfo' => json_encode($lead)];

            //     $response = self::getSomethingUsingGuzzlePost($apiLinkUnsubscribe, $params, 'POST');
            //     if (intval($response['code']) != 0) {
            //         self::writeLog($response['message'], 'Zoho MA Error 0');
            //     }

            //     // then subscribe to repeated donors
            //     $list = $next_list;
            //     if ($donationMethod) {
            //         $lead[$fieldSecondDonationMethod] = $donationMethod;
            //     }
            //     if ($donationAmount) {
            //         $lead[$fieldSecondDonationAmount] = $donationAmount;
            //     }
            //     if ($totalAmountDonated) {
            //         $lead[$fieldTotalAmountDonated] = $totalAmountDonated;
            //     }
            //     if ($donationCount) {
            //         $lead[$fieldTotalDonationsMade] = $donationCount;
            //     }
            // } elseif ($donationCount > 2) {
            //     $list = $next_list;
            //     if ($totalAmountDonated) {
            //         $lead[$fieldTotalAmountDonated] = $totalAmountDonated;
            //     }
            //     if ($donationCount) {
            //         $lead[$fieldTotalDonationsMade] = $donationCount;
            //     }
            // }

            // $params = [
            //     'listkey' => $list,
            //     'leadinfo' => json_encode($lead),
            // ];
            // // self::writeLog($params["leadinfo"], 'leadinfo');

            // $response = self::getSomethingUsingGuzzlePost($apiLinkSubscribe, $params, 'POST');
            // if (intval($response['code']) != 0) {
            //     self::writeLog($response['message'], 'Zoho MA Error 0');
            // }
            return intval($response['code']);
        }
    }

    public static function startAddContact($activityTypeId, $activityId)
    {
        self::writeLog($activityTypeId, 'activity type id');
        self::writeLog($activityId, 'activity id');
        $contactId = self::getContactId($activityId);
        $activityDetails = self::getActivityDetails($activityTypeId, $activityId);

        $subscribeChoice = $activityDetails->getSubscribeChoice();
        self::writeLog($subscribeChoice[0], 'subscribe choice');
        if ($subscribeChoice[0] == 1) { // checkbox value
            self::sendActivityContact($contactId, $activityTypeId, $activityId);
        } else {
            self::writeLog("marketing consent unchecked");
        }
    }

    // public static function createContribution($activityType, $objectId, $objectActivityTypeId)
    // {
    //     self::writeLog($activityType, "inside create contribution");
    //     try {
    //         $activityTypeId = self::getActivityTypeId($activityType);
    //         self::writeLog($activityTypeId, "activityTypeId");
    //         self::writeLog($objectId, "objectId");
    //         $contactId = self::getContactId($objectId);
    //         self::writeLog($contactId, "contactId");
    //         $activityDetails = self::getActivityDetails($activityTypeId, $objectId);
    //         self::writeLog($activityDetails, "activityDetails");
    //         $method = 0;
    //         switch ($activityDetails["donation_method"]) {
    //             case "credit-card":
    //                 $method = 1;
    //                 break;
    //             case "debit-card":
    //                 $method = 2;
    //                 break;
    //             case "cash":
    //                 $method = 3;
    //                 break;
    //             case "check":
    //                 $method = 4;
    //                 break;
    //             case "eft":
    //                 $method = 5;
    //                 break;
    //             default:
    //                 $method = $activityDetails["donation_method"];
    //                 break;
    //         }
    //         $contributionDetails = array(
    //             "contact_id" => $contactId,
    //             "payment_instrument_id" => $method,
    //             "total_amount" => $activityDetails["donation_amount"],
    //             "receive_date" => $activityDetails["created_date"],
    //             "currency" => "USD",
    //             "financial_type_id" => 1,
    //             "contribution_status_id" => 1,
    //         );
    //         self::writeLog($contributionDetails, "contribution details");

    //         $str = $activityDetails["subscribe_choice"];
    //         self::writeLog($str, "str");
    //         $subscribeChoice = str_replace('\u0001', '', $str);
    //         self::writeLog($subscribeChoice, "subscribeChoice");
    //         $subscribeId = 0;
    //         switch ($subscribeChoice) {
    //             case "yes":
    //                 $subscribeId = 1;
    //                 break;
    //             case "no":
    //                 $subscribeId = 2;
    //                 break;
    //             default:
    //                 $subscribeId = $subscribeChoice;
    //                 break;
    //         }

    //         $returnedData = array(
    //             "contribution_details" => $contributionDetails,
    //             "subscribe_id" => $subscribeId,
    //         );

    //         return $returnedData; // for choice of subscription to mailing list
    //     } catch (Exception $e) {
    //         self::writeLog($e->getMessage(), "create contribution error");
    //     }
    // }

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
        $query = "SELECT civicrm_email.email as email
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
        $query = "SELECT civicrm_phone.phone as phone
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

    public static function getAddressInfo($contactID)
    {
        // fetch address info
        $query = "SELECT civicrm_address.street_address AS street_address, 
                    civicrm_address.city AS city, 
                    civicrm_country.name AS country, 
                    civicrm_address.postal_code AS postal_code
                    FROM civicrm_contact, civicrm_address, civicrm_country
                    WHERE civicrm_address.is_primary = 1
                    AND civicrm_contact.id = %1
                    AND civicrm_contact.id = civicrm_address.contact_id
                    AND civicrm_contact.id = civicrm_address.contact_id
                    AND civicrm_country.id = civicrm_address.country_id";
        $p = [1 => [$contactID, 'Integer']];
        $dao = CRM_Core_DAO::executeQuery($query, $p);

        $addressInfo = [];
        if ($dao->fetch()) {
            $addressInfo["street_address"] = $dao->street_address;
            $addressInfo["city"] = $dao->city;
            $addressInfo["country"] = $dao->country;
            $addressInfo["postal_code"] = $dao->postal_code;
        }
        return $addressInfo;
    }
}
