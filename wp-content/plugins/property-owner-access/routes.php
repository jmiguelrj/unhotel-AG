<?php
Routes::map('poa/:lang/admin/properties', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $controllerPropertyProfile = new PropertyOwnerController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'POST') {
        $controllerPropertyProfile->create($_POST);
    }
    $controllerPropertyProfile->list();
});
Routes::map('poa/:lang/admin/properties/:id', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $controllerPropertyProfile = new PropertyOwnerController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PATCH') {
        $controllerPropertyProfile->patch($params['id'], $_POST);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
        $controllerPropertyProfile->delete($params['id']);
    }
    $controllerPropertyProfile->list($params['id'] ?? null);
});

Routes::map('poa/:lang/admin/performance', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $dataType = isset($_GET['dataType']) ? $_GET['dataType'] : 'reservations';
    $controllerPerformancePortal = new PerformancePortalController();
    $controllerPerformancePortal->list(
        $dataType,
        $_GET['categories'] ?? null,
        $_GET['apartments'] ?? null,
        $_GET['checkinFrom'] ?? null,
        $_GET['checkinTo'] ?? null
    );
});

Routes::map('poa/:lang/admin/performance/export', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $withFilters = isset($_GET['withFilters']) ? $_GET['withFilters'] === 'true' : true;
    $exportFilePath = ExportService::generateExport(
        $_GET['categories'] ?? null,
        $_GET['apartments'] ?? null,
        $_GET['checkinFrom'] ?? null,
        $_GET['checkinTo'] ?? null,
        $withFilters
    );
    if (file_exists($exportFilePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($exportFilePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($exportFilePath));
        readfile($exportFilePath);
        exit;
    } else {
        echo "Error: File not found.";
    }
});

Routes::map('poa/:lang/admin/expenses', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $controllerExpenses = new ExpensesController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'POST') {
        $controllerExpenses->create($_POST);
    }
    $controllerExpenses->list();
});
Routes::map('poa/:lang/admin/expenses/:id', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $controllerExpenses = new ExpensesController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
        if ($_POST['_method'] === 'PATCH') {
            $controllerExpenses->patch($params['id'], $_POST);
        }
        if ($_POST['_method'] === 'DELETE') {
            $controllerExpenses->delete($params['id']);
        }
    }
    $controllerExpenses->list($params['id'] ?? null);
});
Routes::map('poa/:lang/admin/transfers', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $controllerTransfers = new TransfersController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'POST') {
        $controllerTransfers->create($_POST);
    }
    $controllerTransfers->list();
});
Routes::map('poa/:lang/admin/transfers/:id', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $controllerTransfers = new TransfersController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PATCH') {
        $controllerTransfers->patch($params['id'], $_POST);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE') {
        $controllerTransfers->delete($params['id']);
    }
    $controllerTransfers->list($params['id'] ?? null);
});
Routes::map('poa/:lang/properties', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $controllerProperties = new PropertiesController();
    $controllerProperties->list();
});
Routes::map('poa/:lang/properties/:id/*', function ($params) {
    changeLanguage(((isset($params['lang'])) ? $params['lang'] : ''));
    $controllerProperties = new PropertiesController();
    $controllerProperties->detail($params['id'], $_GET['checkinFrom'] ?? null, $_GET['checkinTo'] ?? null, isset($_GET['pdf']) ? true : false);
});
