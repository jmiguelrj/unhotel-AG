<?php

use Corcel\Model\User;
use Corcel\Model\Meta\UserMeta;

class PropertyOwnerController extends Controller
{
    function __construct()
    {
        parent::__construct();
        // Check if user is authorized
        checkAuthorized('administrator');
    }

    public function list($id = null)
    {
        // Define the variables
        $propertyOwner = null;
        $excludedRoomId = 0;

        // Get the transfer from the database
        if ($id) {
            $propertyOwner = PropertyOwner::find($id);
            $excludedRoomId = $propertyOwner->room_id;
        }

        $propertyOwners = PropertyOwner::all();
        $properties = Property::whereNotIn('id', function ($query) use ($excludedRoomId) {
            $query->select('room_id')
                ->from('poa_property_owners')
                ->where('room_id', '!=', $excludedRoomId);
        })
            ->orderBy('name')
            ->get();

        $users = User::whereIn('ID', function ($query) {
            $query->select('user_id')
                ->from('usermeta')
                ->where('meta_key', 'wp_capabilities')
                ->where('meta_value', 'like', '%host%');
        })
            ->orderBy('display_name')
            ->get();

        echo $this->blade->run("admin.property", [
            'notifications' => extractSessionNotifications(),
            'propertyOwner' => $propertyOwner,
            'propertyOwners' => $propertyOwners,
            'properties' => sortProperties($properties),
            'users' => $users,
        ]);
    }

    public function create($data)
    {
        // Define the fields that are required
        $optionalFields = ['note'];
        $requiredFields = ['user_id', 'room_id'];
        // Validate the data
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo $field . '<br>';
                exit();
                $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => $field . ' is required.'];
                header('Location: '.getPoaUrl('admin/properties'));
                exit();
            }
        }
        // Create a new Property Owner object
        $propertyOwner = new PropertyOwner();
        // Set the properties of the expense
        foreach (array_merge($optionalFields, $requiredFields) as $field) {
            if (isset($data[$field])) {
                $propertyOwner->$field = $data[$field];
            }
        }
        // Handle the attachment
        $fileTypes = ['contract', 'documents'];
        foreach ($fileTypes as $fileType) {
            if (isset($_FILES[$fileType]) && !empty($_FILES[$fileType]['tmp_name'])) {
                $propertyOwner->$fileType = uploadFile($_FILES[$fileType], 'property', POA_FOLDER_PROPERTIES, ((!empty($propertyOwner->$fileType)) ? $propertyOwner->$fileType : ''));
            } else {
                continue;
            }
        }
        // Save the entry to the database and handle the notifications
        if ($propertyOwner->save()) {

            // Handle commissions
            if (
                isset($data['percentage']) && !empty($data['percentage']) &&
                isset($data['date_from']) && !empty($data['date_from']) &&
                isset($data['date_to']) && !empty($data['date_to'])
            ) {

                $combined = [];

                for ($i = 0; $i < count($data['percentage']); $i++) {
                    $newPeriod = [
                        'percentage' => $data['percentage'][$i],
                        'date_from' => $data['date_from'][$i],
                        'date_to' => $data['date_to'][$i],
                    ];

                    $overlap = false;
                    foreach ($combined as $existingPeriod) {
                        if (($newPeriod['date_from'] >= $existingPeriod['date_from'] && $newPeriod['date_from'] <= $existingPeriod['date_to']) ||
                            ($newPeriod['date_to'] >= $existingPeriod['date_from'] && $newPeriod['date_to'] <= $existingPeriod['date_to'])
                        ) {
                            $overlap = true;
                            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'The period from ' . date('d/m/Y', strtotime($newPeriod['date_from'])) . ' to ' . date('d/m/Y', strtotime($newPeriod['date_to'])) . ' is overlapping with an existing period and has been skipped.', 'sticky' => true];
                            break;
                        }
                    }

                    if (!$overlap) {
                        $combined[] = $newPeriod;
                    }
                }

                $commissionsErrors = 0;
                foreach ($combined as $item) {
                    $percentage = new PropertyOwnerCommission();
                    $percentage->property_owner_id = $propertyOwner->id;
                    $percentage->percentage = $item['percentage'];
                    $percentage->date_from = $item['date_from'];
                    $percentage->date_to = $item['date_to'];
                    if (!$percentage->save()) {
                        $commissionsErrors++;
                    }
                }
                if ($commissionsErrors > 0) {
                    $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error creating commissions.'];
                }
            }
            $_SESSION['poa-notifications'][] = ['type' => 'success', 'message' => 'Record created successfully.'];
        } else {
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error creating the record.'];
        }

        // Redirect to the property profile
        header('Location: '.getPoaUrl('admin/properties'));
        exit();
    }

    public function delete($id)
    {
        // Delete the entry from the database
        $propertyOwner = PropertyOwner::find($id);
        if ($propertyOwner) {
            // Delete the commissions
            $commissions = PropertyOwnerCommission::where('property_owner_id', $id)->get();
            if ($commissions) {
                foreach ($commissions as $commission) {
                    $commission->delete();
                }
            }
            // Delete the entry
            if ($propertyOwner->delete()) {
                // Remove the attachment
                $fileTypes = ['contract', 'documents'];
                foreach ($fileTypes as $fileType) {
                    if (!empty($propertyOwner->$fileType)) {
                        removeFile($propertyOwner->$fileType, POA_FOLDER_PROPERTIES);
                    } else {
                        continue;
                    }
                }
                $_SESSION['poa-notifications'][] = ['type' => 'success', 'message' => 'Record deleted successfully.'];
            } else {
                $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error deleting the record.'];
            }
        }
        // Redirect to the admin properties list
        header('Location: '.getPoaUrl('admin/properties'));
        exit();
    }

    public function patch($id, $data)
    {
        // Find the transfer from the database
        $propertyOwner = PropertyOwner::find($id);
        if (!$propertyOwner) {
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error editing the record.'];
            // Redirect to the transfers list
            header('Location: '.getPoaUrl('admin/properties'));
            exit();
        }
        // Define the fields that are required
        $optionalFields = ['note'];
        $requiredFields = ['user_id', 'room_id'];
        // Validate the data
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                echo $field . '<br>';
                exit();
                $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => $field . ' is required.'];
                header('Location: '.getPoaUrl('admin/properties'));
                exit();
            }
        }
        // Set the properties of the expense
        foreach (array_merge($optionalFields, $requiredFields) as $field) {
            if (isset($data[$field])) {
                $propertyOwner->$field = $data[$field];
            }
        }
        // Handle the attachment
        $fileTypes = ['contract', 'documents'];
        foreach ($fileTypes as $fileType) {
            if (isset($_FILES[$fileType]) && !empty($_FILES[$fileType]['tmp_name'])) {
                $propertyOwner->$fileType = uploadFile($_FILES[$fileType], 'property', POA_FOLDER_PROPERTIES, ((!empty($propertyOwner->$fileType)) ? $propertyOwner->$fileType : ''));
            } else {
                continue;
            }
        }

        // Save the entry to the database and handle the notifications
        if ($propertyOwner->save()) {
            // Delete old commissions
            PropertyOwnerCommission::where('property_owner_id', $id)->delete();

            // Handle commissions
            if (
                isset($data['percentage']) && !empty($data['percentage']) &&
                isset($data['date_from']) && !empty($data['date_from']) &&
                isset($data['date_to']) && !empty($data['date_to'])
            ) {
                $combined = [];
                for ($i = 0; $i < count($data['percentage']); $i++) {
                    $combined[] = [
                        'percentage' => $data['percentage'][$i],
                        'date_from' => $data['date_from'][$i],
                        'date_to' => $data['date_to'][$i],
                    ];
                }
                $commissionsErrors = 0;
                foreach ($combined as $item) {
                    $percentage = new PropertyOwnerCommission();
                    $percentage->property_owner_id = $propertyOwner->id;
                    $percentage->percentage = $item['percentage'];
                    $percentage->date_from = $item['date_from'];
                    $percentage->date_to = $item['date_to'];
                    if (!$percentage->save()) {
                        $commissionsErrors++;
                    }
                }
                if ($commissionsErrors > 0) {
                    $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error creating commissions.'];
                }
            }

            $_SESSION['poa-notifications'][] = ['type' => 'success', 'message' => 'Record saved successfully.'];
        } else {
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error saving the record.'];
        }

        // Redirect to the property profile
        header('Location: '.getPoaUrl('admin/properties'));
        exit();
    }
}
