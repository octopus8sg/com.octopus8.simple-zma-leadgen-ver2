<?php

use CRM_Simplezmaleadgen_Utils as U;
use CRM_Simplezmaleadgen_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Simplezmaleadgen_Form_Configuration extends CRM_Core_Form {
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
        U::writeLog($settings, "after_submit");
        $s = CRM_Core_BAO_Setting::setItem($settings, U::SETTINGS_NAME, U::SETTINGS_SLUG);
//        U::writeLog($s);
        CRM_Core_Session::setStatus(E::ts('Settings Saved', ['domain' => 'com.octopus8.simplezmaleadgen']), 'Configuration Updated', 'success');

        parent::postProcess();
    }

    /**
     * Get the fields/elements defined in this form.
     *
     * @return array (string)
     */
    public function getRenderableElementNames() {
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


}
