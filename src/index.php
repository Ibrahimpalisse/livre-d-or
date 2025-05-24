// *** Routes for Comments ***
$router->add('/comments/add', [
    'controller' => 'CommentController',
    'action' => 'add',
    'method' => 'POST'
]);

$router->add('/comments/by-publication', [
    'controller' => 'CommentController',
    'action' => 'byPublication',
    'method' => 'GET'
]);

// *** Routes for Publication Validations ***
$router->add('/publication/validate', [
    'controller' => 'CommentController',
    'action' => 'validatePublication',
    'method' => 'POST'
]);

$router->add('/publication/stats', [
    'controller' => 'CommentController',
    'action' => 'getValidationStats',
    'method' => 'GET'
]); 