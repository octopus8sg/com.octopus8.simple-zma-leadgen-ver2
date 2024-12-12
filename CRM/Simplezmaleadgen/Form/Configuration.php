<?php

use CRM_Simplezmaleadgen_Utils as U;
use CRM_Simplezmaleadgen_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Simplezmaleadgen_Form_Configuration extends CRM_Core_Form
{
    public function buildQuickForm()
    {
        //        U::writeLog("start conf");
        $textsize = ['size' => 77];
        $this->add('checkbox', U::SAVE_LOG['slug'], U::SAVE_LOG['name']);
        $this->add('static', U::SAVE_LOG['slug'] . "_description", U::SAVE_LOG['slug'], U::SAVE_LOG['description']);

        $this->add('checkbox', U::SEND_CONTACT['slug'], U::SEND_CONTACT['name']);
        $this->add('static', U::SEND_CONTACT['slug'] . "_description", U::SEND_CONTACT['slug'], U::SEND_CONTACT['description']);

        $this->add('text', U::CLIENT_ID['slug'], U::CLIENT_ID['name'], $textsize, true);
        $this->add('static', U::CLIENT_ID['slug'] . "_description", U::CLIENT_ID['slug'], U::CLIENT_ID['description']);

        $this->add('text', U::CLIENT_SECRET['slug'], U::CLIENT_SECRET['name'], $textsize, TRUE);
        $this->add('static', U::CLIENT_SECRET['slug'] . "_description", U::CLIENT_SECRET['slug'], U::CLIENT_SECRET['description']);

        $this->add('text', U::REFRESH_TOKEN['slug'], U::REFRESH_TOKEN['name'], $textsize, true);
        $this->add('static', U::REFRESH_TOKEN['slug'] . "_description", U::REFRESH_TOKEN['slug'], U::REFRESH_TOKEN['description']);

        $this->add('text', U::SERVER_URI['slug'], U::SERVER_URI['name'], $textsize, TRUE);
        $this->add('static', U::SERVER_URI['slug'] . "_description", U::SERVER_URI['slug'], U::SERVER_URI['description']);

        $this->add('text', U::MAILING_LIST_NAME['slug'], U::MAILING_LIST_NAME['name'], $textsize, TRUE);
        $this->add('static', U::MAILING_LIST_NAME['slug'] . "_description", U::MAILING_LIST_NAME['slug'], U::MAILING_LIST_NAME['description']);

        $this->addElement('html', '<td colspan="2"></td>');

        $this->add('select', U::ACTIVITY_TYPE['slug'], U::ACTIVITY_TYPE['name'], U::getActivityTypeOptions(), $textsize, TRUE, ['placeholder' => ts('- Please Select'),]);
        $this->add('static', U::ACTIVITY_TYPE['slug'] . "_description", U::ACTIVITY_TYPE['slug'], U::ACTIVITY_TYPE['description']);

        $this->add('select', U::CUSTOM_GROUP['slug'], U::CUSTOM_GROUP['name'], U::getCustomGroupOptions(), $textsize, TRUE);
        $this->add('static', U::CUSTOM_GROUP['slug'] . "_description", U::CUSTOM_GROUP['slug'], U::CUSTOM_GROUP['description']);

        $this->add('select', U::CUSTOM_FIELD['slug'], U::CUSTOM_FIELD['name'], [], $textsize, TRUE);

        if (CRM_Utils_System::currentPath() == 'civicrm/simplezmaleadgen/configuration') {
            CRM_Core_Resources::singleton()->addScriptFile('com.octopus8.simplezmaleadgen', 'CRM\Simplezmaleadgen\Form\updateCustomFieldsOptions.js', 100, 'html-header');
            CRM_Core_Resources::singleton()->addStyleFile('com.octopus8.simplezmaleadgen', 'CRM\Simplezmaleadgen\Form\style.css');
        }
        $this->add('static', U::CUSTOM_FIELD['slug'] . "_description", U::CUSTOM_FIELD['slug'], U::CUSTOM_FIELD['description']);

        $this->assign('leftEmptySpace', '<div id="leftEmptySpace"></div>');

        // Display the last saved custom fields table after the custom fields select element
        $this->assign('lastSavedCustomFieldsTable', $this->displayLastSavedCustomField());

        $this->addButtons([
            [
                'type' => 'submit',
                'name' => E::ts('Submit'),
                'isDefault' => TRUE,
            ],
        ]);
        $this->assign('elementNames', $this->getRenderableElementNames());
        parent::buildQuickForm();
    }

    public function setDefaultValues()
    {
        $defaults = [];
        $settings = CRM_Core_BAO_Setting::getItem(U::SETTINGS_NAME, U::SETTINGS_SLUG);
        U::writeLog($settings, "before save");
        $customGroupId = U::getCustomGroupId();
        U::writeLog($customGroupId, 'customGroupId');
        $customFieldIds = U::getCustomFieldId();
        U::writeLog($customFieldIds, 'customFieldIds');
        if (!empty($settings)) {
            $defaults = $settings;
        }

        return $defaults;
    }

    public function postProcess()
    {
        $values = $this->exportValues();
        $settings[U::SAVE_LOG['slug']] = $values[U::SAVE_LOG['slug']];
        $settings[U::SEND_CONTACT['slug']] = $values[U::SEND_CONTACT['slug']];
        $settings[U::CLIENT_ID['slug']] = $values[U::CLIENT_ID['slug']];
        $settings[U::SERVER_URI['slug']] = $values[U::SERVER_URI['slug']];
        $settings[U::REFRESH_TOKEN['slug']] = $values[U::REFRESH_TOKEN['slug']];
        $settings[U::CLIENT_SECRET['slug']] = $values[U::CLIENT_SECRET['slug']];
        $settings[U::MAILING_LIST_NAME['slug']] = $values[U::MAILING_LIST_NAME['slug']];
        $settings[U::ACTIVITY_TYPE['slug']] = $values[U::ACTIVITY_TYPE['slug']];
        $settings[U::CUSTOM_GROUP['slug']] = $values[U::CUSTOM_GROUP['slug']];
        $settings[U::CUSTOM_FIELD['slug']] = $values[U::CUSTOM_FIELD['slug']];
        U::writeLog($settings, "after_submit");
        $s = CRM_Core_BAO_Setting::setItem($settings, U::SETTINGS_NAME, U::SETTINGS_SLUG);

        // Update the table HTML with the latest custom field data
        $latestCustomFieldHtml = $this->displayLastSavedCustomField();

        // Build the JavaScript to update the table HTML
        $js = "
            (function($) {
                $(document).ready(function() {
                    window.location.reload();
                });
            })(CRM.$);
        ";

        // Add the JavaScript to update the table HTML
        CRM_Core_Resources::singleton()->addScript($js, 'inline');

        //        U::writeLog($s);
        CRM_Core_Session::setStatus(E::ts('Settings Saved', ['domain' => 'com.octopus8.simplezmaleadgen']), 'Configuration Updated', 'success');

        parent::postProcess();
    }

    /**
     * Get the fields/elements defined in this form.
     *
     * @return array (string)
     */
    public function getRenderableElementNames()
    {
        // The _elements list includes some items which should not be
        // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
        // items don't have labels.  We'll identify renderable by filtering on
        // the 'label'.
        $elementNames = array();
        foreach ($this->_elements as $element) {
            $label = $element->getLabel();
            if (!empty($label)) {
                $elementNames[] = $element->getName();
            }
        }
        return $elementNames;
    }

    public function displayLastSavedCustomField()
    {
        $customFieldId = U::getCustomFieldId();

        // Start building the table
        $html = '<div id="lastSavedCustomFieldsTableContainer"><table id="lastSavedCustomFieldsTable">';

        // Add a header row
        $html .= '<tr><th>Last Saved Custom Field</th><th>Custom Field Group</th></tr><tbody>';

        $customField = U::getCustomFieldLabelAndGroupName($customFieldId);

        // Check if custom field data is available
        if (!empty($customField)) {
            // Add a row for each field with its last saved value
            $html .= '<tr><td>' . htmlspecialchars($customField['label']) . '</td><td>' . htmlspecialchars($customField['custom_group_id:label']) . '</td></tr>';
        } else {
            $html .= '<tr><td colspan="2">No custom field data available</td></tr>';
        }
        // Close the table
        $html .= '</tbody></table></div>';

        // Display the table
        return $html;
    }

}
