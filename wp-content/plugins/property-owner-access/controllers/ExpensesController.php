<?php
class ExpensesController extends Controller
{
    function __construct()
    {
        parent::__construct();
        // Check if user is authorized
        checkAuthorized('administrator');
    }

    public function list($expenseId = null)
    {
        // Define the variables
        $expense = null;

        // Paginate expenses (100 per page)
        $rowsPerPage = 100;
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $expenses = Expense::orderBy('date', 'desc')->paginate($rowsPerPage, ['*'], 'page', $currentPage);
        $expenses->appends($_GET);
        
        $properties = Property::all();
        $categories = ExpenseCategory::all();

        // Restructure the categories
        $restructuredCategories = [];
        foreach ($categories as $category) {
            if ($category->parent_id) {
                $restructuredCategories[$category->parent_id]['subcategories'][] = [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            } else {
                $restructuredCategories[$category->id] = [
                    'id' => $category->id,
                    'name' => $category->name,
                ];
            }
        }

        // Get the expense from the database
        if ($expenseId) {
            $expense = Expense::find($expenseId);
        }

        // Render the view
        echo $this->blade->run("admin.expenses", [
            'notifications' => extractSessionNotifications(),
            'expense' => $expense,
            'expenses' => $expenses,
            'properties' => sortProperties($properties),
            'categories' => $restructuredCategories,
        ]);
    }

    public function create($data)
    {
        // Define the fields that are required
        $optionalFields = ['note', 'owner'];
        $requiredFields = ['date', 'room_id', 'expenses_category_id', 'amount'];
        // Validate the data
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => $field . ' is required.'];
                header('Location: '.getPoaUrl('admin/expenses'));
                exit();
            }
        }
        // Create a new Expense object
        $expense = new Expense();
        // Set the properties of the expense
        foreach (array_merge($optionalFields, $requiredFields) as $field) {
            if (isset($data[$field])) {
                $expense->$field = $data[$field];
            }
        }
        // Handle the attachment
        if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['tmp_name'])) {
            $expense->attachment = uploadFile($_FILES['attachment'], 'expense', POA_FOLDER_EXPENSES, ((!empty($expense->attachment)) ? $expense->attachment : ''));
        }
        // Translate the tables
        translatePoaTables();
        // Save the expense to the database and handle the notifications
        if ($expense->save()) {
            $_SESSION['poa-notifications'][] = ['type' => 'success', 'message' => 'Record created successfully.'];
        } else {
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error creating the record.'];
        }
        // Redirect to the expenses list
        header('Location: '.getPoaUrl('admin/expenses'));
        exit();
    }

    public function delete($id)
    {
        // Delete the expense from the database
        $expense = Expense::find($id);
        if ($expense) {
            if ($expense->delete()) {
                // Remove the attachment
                if (!empty($expense->attachment)) {
                    removeFile($expense->attachment, POA_FOLDER_EXPENSES);
                }
                $_SESSION['poa-notifications'][] = ['type' => 'success', 'message' => 'Record deleted successfully.'];
            } else {
                $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error deleting the record.'];
            }
        }
        // Redirect to the expenses list
        header('Location: '.getPoaUrl('admin/expenses'));
        exit();
    }

    public function patch($id, $data)
    {
        // Find the expense from the database
        $expense = Expense::find($id);
        if (!$expense) {
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error editing the record.'];
            // Redirect to the expenses list
            header('Location: '.getPoaUrl('admin/expenses'));
            exit();
        }
        // Define the fields that are required
        $optionalFields = ['note', 'owner'];
        $requiredFields = ['date', 'room_id', 'expenses_category_id', 'amount'];
        // Validate the data
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => $field . ' is required.'];
                header('Location: '.getPoaUrl('admin/expenses/'.$id));
                exit();
            }
        }
        // Set the properties of the expense
        foreach (array_merge($optionalFields, $requiredFields) as $field) {
            if (isset($data[$field])) {
                $expense->$field = $data[$field];
            }
        }
        // Handle the attachment
        if (isset($_FILES['attachment']) && !empty($_FILES['attachment']['tmp_name'])) {
            $expense->attachment = uploadFile($_FILES['attachment'], 'expense', POA_FOLDER_EXPENSES, ((!empty($expense->attachment)) ? $expense->attachment : ''));
        }
        // Translate the tables
        translatePoaTables();
        // Save the expense to the database and handle the notifications
        if ($expense->save()) {
            $_SESSION['poa-notifications'][] = ['type' => 'success', 'message' => 'Record edited successfully.'];
        } else {
            $_SESSION['poa-notifications'][] = ['type' => 'error', 'message' => 'Error editing the record.'];
        }
        // Redirect to the expenses list
        header('Location: '.getPoaUrl('admin/expenses'));
        exit();
    }
}
