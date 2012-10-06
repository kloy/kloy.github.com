<?php
// in application/routes.php
Route::post('user', function()
{
    $data = array('userId' => 1);
    return Response::make(json_encode($data), 200,
        array('Content-Type' => 'application/json'));
});

Route::get('user', function()
{
    $data = array('name' => 'Chris');
    return Response::make(json_encode($data), 200,
        array('Content-Type' => 'application/json'));
});

Route::delete('user', function()
{
    $data = array('status' => "true");
    return Response::make(json_encode($data), 200,
        array('Content-Type' => 'application/json'));
});