<?php

require_once 'simplezmaleadgen.civix.php';

// phpcs:disable
use CRM_Simplezmaleadgen_ExtensionUtil as E;
use CRM_Simplezmaleadgen_Utils as U;

global $originalState;
global $modifiedState;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function simplezmaleadgen_civicrm_config(&$config)
{
    _simplezmaleadgen_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function simplezmaleadgen_civicrm_install()
{
    _simplezmaleadgen_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function simplezmaleadgen_civicrm_postInstall()
{
    _simplezmaleadgen_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function simplezmaleadgen_civicrm_uninstall()
{
    _simplezmaleadgen_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function simplezmaleadgen_civicrm_enable()
{
    _simplezmaleadgen_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function simplezmaleadgen_civicrm_disable()
{
    _simplezmaleadgen_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function simplezmaleadgen_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL)
{
    return _simplezmaleadgen_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function simplezmaleadgen_civicrm_entityTypes(&$entityTypes)
{
    _simplezmaleadgen_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function simplezmaleadgen_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function simplezmaleadgen_civicrm_navigationMenu(&$menu)
{
    _simplezmaleadgen_civix_insert_navigation_menu($menu, 'Administer/CiviContribute', [
        'label' => E::ts('Configure Simple ZMA LeadGen'),
        'name' => 'configure_simplezmaleadgen',
        'url' => 'civicrm/simplezmaleadgen/configuration',
        'permission' => 'access CiviCRM',
        'operator' => 'OR',
        'separator' => 1,
    ]);
    _simplezmaleadgen_civix_navigationMenu($menu);
}

function simplezmaleadgen_civicrm_pre($op, $objectName, $objectId, &$objectRef)
{
    // Store the original state of the object
    global $originalState;
    if ($objectName === 'Contribution') {
        if ($op === 'edit') {
            //            $originalState = (Array) $objectRef;
            $prev_status = civicrm_api4('Contribution', 'get', [
                'select' => [
                    'contribution_status_id',
                ],
                'where' => [

                    ['id', '=', $objectId],
                ],
                'limit' => 0,
                'checkPermissions' => FALSE,
            ])->first();
            $originalState = (Array) $prev_status;
            //            U::writeLog($prev_status, 'simplezmaleadgen_civicrm_pre');

        }
    }
}

// function simplezmaleadgen_civicrm_post($op, $objectName, $objectId, &$objectRef)
// {
//     global $modifiedState;
//     //    $params = (array) $object;
// //    U::writeLog($op . ':' . $objectName, 'simplezmaleadgen_civicrm_post');
//     if ($objectName === 'Contribution') {
//         if ($op === 'edit') {
//             $modifiedState = (Array) $objectRef;
//         }
//     }

// }

// global $subscribeChoice;
function simplezmaleadgen_civicrm_postCommit($op, $objectName, $objectId, &$object)
{

    U::writeLog($op, 'op');
    U::writeLog($objectName, 'objectName');
    U::writeLog($objectId, 'objectId');
    U::writeLog($object, 'object');
    // create contribution from ninja form activity submission
    // if ($objectName === "Activity") {
    //     U::writeLog("Inside activity", "Debugging");
    //     if ($op === 'create') {
    //         U::writeLog("Inside edit", "Debugging");
    //         $activityType = "Donation Form";
    //         global $subscribeChoice;
    //         U::writeLog($activityType, 'activity type before create contribution');
    //         U::writeLog($objectId, 'object id before create contribution');
    //         $receivedData = U::createContribution($activityType, $objectId);

    //         $contributionDetails = $receivedData["contribution_details"];
    //         U::writeLog($contributionDetails, 'contribution detailin postcommit');
    //         $subscribeChoice = $receivedData["subscribe_id"];
    //         U::writeLog($contributionDetails, 'contribution detailin postcommit');

    //         // create contribution with apiv4
    //         civicrm_api4("Contribution", "create", ["values" => $contributionDetails, "checkPermissions" => TRUE]);
    //     }
    // }

    // add contact into mailing subscription upon ninja form activity submission
    if ($objectName == 'Activity') {
        // U::writeLog($op);
        if ($op === 'create') {
            // Get the activity type ID for the form submission activity
            $activityTypeId = U::getActivityTypeId();
            // U::writeLog($activityTypeId, 'activityTypeId');

            // Check if the created activity is of the form submission
            if ($object->activity_type_id == $activityTypeId) {
                // U::writeLog("before start add contact");
                U::startAddContact($activityTypeId, $objectId);
            }
        }
    }

    // not used for now
    // Compare the original and modified states of the object
    global $originalState;
    global $modifiedState;

    if ($objectName === 'Contribution') {
        //         global $subscribeChoice;
//         if ($subscribeChoice = 1) {
        if ($op === 'edit') {
            $prev_status_id = $originalState['contribution_status_id'];
            $new_status_id = $modifiedState['contribution_status_id'];
            $prev_status = U::getContributionStatusName($prev_status_id);
            $new_status = U::getContributionStatusName($new_status_id);
            //                U::writeLog($originalState, '$originalState simplezmaleadgen_civicrm_postCommit');
//                U::writeLog($modifiedState, '$modifiedState simplezmaleadgen_civicrm_postCommit');
//                U::writeLog($prev_status, '$prev_status simplezmaleadgen_civicrm_postCommit');
//                U::writeLog($new_status, '$new_status simplezmaleadgen_civicrm_postCommit');
            if ($new_status == 'Completed' && $prev_status != $new_status) {
                U::sendContributionContact($object);

            }
        }
        if ($op === 'create') {
            $new_status_id = $object->contribution_status_id;
            $new_status = U::getContributionStatusName($new_status_id);
            if ($new_status == 'Completed') {
                U::sendContributionContact($object);
            }
        }
        //         }
    }

}
