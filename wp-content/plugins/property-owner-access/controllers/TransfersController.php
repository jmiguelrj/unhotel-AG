<?php
class TransfersController extends Controller
{
    function __construct()
    {
        parent::__construct();
        // Check if user is authorized
        checkAuthorized('administrator');
    }
    
    public function list($transferId = null)
    {
        // Define the variables
        $transfer = null;

         $rowsPerPage = 100;
         $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        // Get all the transfers, properties and methods
        // $transfers = Transfer::orderBy('date', 'desc')->get()
         $transfers = Transfer::orderBy('date', 'desc')->paginate($rowsPerPage, ['*'], 'page', $currentPage);
        $transfers->appends($_GET);
        $properties = Property::all();
        $methods = TransferMethod::all();

        // Get the transfer from the database
        if ($transferId) {
            $transfer = Transfer::find($transferId);
        }

        // Render the view
        echo $this->blade->run("admin.transfers", [
            'notifications' => extractSessionNotifications(),
            'transfer' => $transfer,
            'transfers' => $transfers,
            'properties' => sortProperties($properties),
            'methods' => $methods,
        ]);
    }

    public function create($data)
    {
        // Define the fields that are required
        $optionalFields = ['note'];
        $requiredFields = ['date', 'room_id', 'transfer_method_id', 'amount'];
        // Validate the data
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => $field . ' is required.'];
                header('Location: '.getPoaUrl('admin/transfers'));
                exit();
            }
        }
        // Create a new Transfer object
        $transfer = new Transfer();
        // Set the properties of the transfer
        foreach (array_merge($optionalFields, $requiredFields) as $field) {
            if (isset($data[$field])) {
                $transfer->$field = $data[$field];
            }
        }
        // Handle the attachment
        if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['tmp_name'])) {
            $transfer->attachment = uploadFile($_FILES['attachment'], 'transfer', POA_FOLDER_TRANSFERS, ( ( !empty($transfer->attachment) ) ? $transfer->attachment : '' ));
        }
        // Translate the tables
        translatePoaTables();
        // Save the transfer to the database and handle the notifications
        if ($transfer->save()) {
            $_SESSION['poa-notifications'][] = ['type' => 'success', 'message' => 'Record created successfully.'];
        } else {
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error creating the record.'];
        }

        // Redirect to the transfers list
        header('Location: '.getPoaUrl('admin/transfers'));
        exit();
    }

    public function delete($id)
    {
        // Delete the transfer from the database
        $transfer = Transfer::find($id);
        if ($transfer) {
            if ($transfer->delete()) {
                // Remove the attachment
                if( !empty($transfer->attachment) ) {
                    removeFile($transfer->attachment, POA_FOLDER_TRANSFERS);
                }
                $_SESSION['poa-notifications'][] = ['type' => 'success', 'message' => 'Record deleted successfully.'];
            } else {
                $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error deleting the record.'];
            }
        }
        // Redirect to the transfers list
        header('Location: '.getPoaUrl('admin/transfers'));
        exit();
    }

    public function patch($id, $data)
    {
        // Find the transfer from the database
        $transfer = Transfer::find($id);
        if (!$transfer) {
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error editing the record.'];
            // Redirect to the transfers list
            header('Location: '.getPoaUrl('admin/transfers'));
            exit();
        }
        // Define the fields that are required
        $optionalFields = ['note'];
        $requiredFields = ['date', 'room_id', 'transfer_method_id', 'amount'];
        // Validate the data
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => $field . ' is required.'];
                header('Location: '.getPoaUrl('admin/transfers/'.$id));
                exit();
            }
        }
        // Set the properties of the transfer
        foreach (array_merge($optionalFields, $requiredFields) as $field) {
            if (isset($data[$field])) {
                $transfer->$field = $data[$field];
            }
        }
        // Handle the attachment
        if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['tmp_name'])) {
            $transfer->attachment = uploadFile($_FILES['attachment'], 'transfer', POA_FOLDER_TRANSFERS, ( ( !empty($transfer->attachment) ) ? $transfer->attachment : '' ));
        }
        // Translate the tables
        translatePoaTables();
        // Save the transfer to the database
        if ($transfer->save()) {
            $_SESSION['poa-notifications'][] = ['type' => 'success', 'message' => 'Record edited successfully.'];
        } else {
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error editing the record.'];
        }
        // Redirect to the transfers list
        header('Location: '.getPoaUrl('admin/transfers'));
        exit();
    }
}
