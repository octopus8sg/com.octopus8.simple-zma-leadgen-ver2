# com.octopus8.simplezmaleadgen

![Screenshot](/images/screenshot.png)

After Submission of a Contribution, this extension adds it's Contact to one of two lists in Zoho Marketing Automation.

The lead is added after the contact confirms subscription.

If the email is not valid, the lead is not added.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.2+
* CiviCRM (*FIXME: Version number*)

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl com.octopus8.simplezmaleadgen@https://github.com/FIXME/com.octopus8.simplezmaleadgen/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/com.octopus8.simplezmaleadgen.git
cv en simplezmaleadgen
```

After installation you should get Zoho Marketing Automation API Credentials and enter them to the Extension Configuration.

## Getting Started - Getting Zoho Marketing Automation API Credentials

1. Create your account in Zoho Accounts here, using Google credentials: `https://accounts.zoho.com/`.

2. Access your Marketing Automation Dashboard: `https://ma.zoho.com/`, in the same browser or use your Google credentials.

3. To create Zoho API OAuth `client_id` and `client_secret`, follow these steps:

   a. Go to the [Zoho Developer Console](https://api-console.zoho.com).
   
   b. Click on "Add Client ID".

   c. Select "Server-based Applications" as the Client Type.

   d. Enter a name for your Client ID.

   e. Enter `https://www.google.com/` as the Authorized Redirect URI and Server URI.

   f. Click on the "Create" button.

   g. Copy the `Client ID` and `Client Secret` values and store them securely.

   h. Under the "Settings" tab, check the "Use the same OAuth credentials for all data centers" checkbox.

4. To generate an access code, follow these steps:

   a. In the same browser, go to `https://accounts.zoho.com/oauth/v2/auth?response_type=code&client_id={client_id}&scope=ZohoMarketingAutomation.campaign.ALL,ZohoMarketingAutomation.lead.ALL&redirect_uri=https://www.google.com/&access_type=offline` and replace `{client_id}` with your actual `Client ID`.

   b. Click "OK".

   c. After being redirected to `https://www.google.com/`, copy the code from the address bar.

5. To generate a Refresh Token using the access code, follow these steps:

   a. Install and open [Postman](https://www.postman.com/downloads/).

   b. Open a new request tab in Postman.

   c. In the URL field, enter `https://accounts.zoho.com/oauth/v2/token`.

   d. In the "Headers" tab, add a new key-value pair with "Content-Type" as the key and "application/x-www-form-urlencoded" as the value.

   e. In the "Body" tab, select "x-www-form-urlencoded" as the type.

   f. Add the following key-value pairs to the "Body":
   
      - "grant_type" as the key and "authorization_code" as the value.
      - "client_id" as the key and your actual `Client ID` as the value.
      - "client_secret" as the key and your actual `Client Secret` as the value.
      - "redirect_uri" as the key and `https://www.google.com/` as the value.
      - "code" as the key and the access code you obtained earlier as the value.

   g. Click on "Send".

   h. The response will contain a Refresh Token that you can use to authenticate API requests.

And that's it! You have now generated a Refresh Token for your Zoho API OAuth credentials using Postman.

## Known Issues

(* FIXME *)
