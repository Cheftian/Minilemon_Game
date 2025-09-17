<?php

/** @var \Laravel\Lumen\Routing\Router $router */


$router->get('/', function () {
    return redirect('/signin.html');
});

$router->options('{any:.*}', function () {
    return response('OK', 200);
});



// User routes
$router->group(['prefix' => 'users'], function () use ($router) {
    $router->get('/', 'UserController@index');
    $router->get('{id}', 'UserController@show');
    $router->post('/', 'UserController@store');
    $router->put('{id}', 'UserController@update');
    $router->delete('{id}', 'UserController@destroy');
});

// User Autentikasi
$router->post('/login', 'UserController@login');

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->post('/logout', 'UserController@logout');
});


// Character routes
$router->group(['prefix' => 'characters'], function () use ($router) {
    $router->get('/', 'CharacterController@index');
    $router->get('{id}', 'CharacterController@show');
    $router->post('/', 'CharacterController@store');
    $router->put('{id}', 'CharacterController@update');
    $router->delete('{id}', 'CharacterController@destroy');
});

// Clothes routes (Skin, Top, Shoes, Hair, Bottom, Accessory, Jacket, ClothesInSet)
$router->group(['prefix' => 'clothes'], function () use ($router) {
    $router->get('{type}', 'ClothesController@index');
    $router->get('{type}/{id}', 'ClothesController@show');
    $router->post('{type}', 'ClothesController@store');
    $router->put('{type}/{id}', 'ClothesController@update');
    $router->delete('{type}/{id}', 'ClothesController@destroy');
});

// Space routes
$router->group(['prefix' => 'spaces', 'middleware' => 'auth'], function () use ($router) {
    $router->post('/', 'SpaceController@create');              
    $router->post('/join', 'SpaceController@join');            
    $router->put('{id}', 'SpaceController@update');            
    $router->get('/', 'SpaceController@index');                
    $router->get('{id}', 'SpaceController@show');              
    $router->delete('{id}', 'SpaceController@destroy');   
});

// SpacesMember route
$router->group(['prefix' => 'spacemember'], function () use ($router) {
    $router->get('/all', 'SpaceMemberController@getAllMembers');            
    $router->get('/{spaceId}', 'SpaceMemberController@index');
    $router->get('byuser/{userId}', 'SpaceMemberController@getSpacesByUser');     
    $router->get('/{spaceId}/{userId}', 'SpaceMemberController@getBySpaceAndUser');            
    $router->put('/status/{id}', 'SpaceMemberController@updateStatus');   
    $router->put('/role/{id}', 'SpaceMemberController@updateRole');       
    $router->delete('/{id}', 'SpaceMemberController@destroy');            
});


// Room routes
$router->group(['prefix' => 'rooms'], function () use ($router) {
    $router->get('/', 'RoomController@index');
    $router->get('space/{spaceId}', 'RoomController@index');
    $router->get('/{id}', 'RoomController@show');
    $router->post('/', 'RoomController@store');
    $router->put('/{id}', 'RoomController@update');
    $router->delete('/{id}', 'RoomController@destroy');
});

// User Position routes
$router->group(['prefix' => 'userposition'], function () use ($router) {
    $router->get('/', 'UserPositionController@index');
    $router->get('filter', 'UserPositionController@filter');
    $router->post('enter/{SpacesMember_ID}', 'UserPositionController@enter');
    $router->put('moveroom/{SpacesMember_ID}', 'UserPositionController@moveRoom');
    $router->put('move/{SpacesMember_ID}', 'UserPositionController@move');
    $router->delete('leave/{SpacesMember_ID}', 'UserPositionController@leave');
    $router->get('{SpacesMember_ID}', 'UserPositionController@show');
    $router->put('/full/{SpacesMember_ID}', 'UserPositionController@update');

    $router->get('/same_room/{SpacesMember_ID}', 'UserPositionController@getSameRoomPositions');


    $router->put('/{SpacesMember_ID}/enterchat/{ChatArea_ID}', 'UserPositionController@enterAreaChat');
    $router->put('/{SpacesMember_ID}/leavechat', 'UserPositionController@leaveAreaChat');

});

// Chat Area routes
$router->group(['prefix' => 'chat_areas'], function () use ($router) {
    $router->post('/create/{spacesId}', 'ChatAreaController@create');
    $router->delete('/delete/{spacesId}', 'ChatAreaController@deleteIfEmpty');
    $router->get('/', 'ChatAreaController@getAll');
    $router->get('object/{objectId}', 'ChatAreaController@getByObjectId');
    $router->get('/room/{roomId}', 'ChatAreaController@getByRoom');
    $router->get('/{id}', 'ChatAreaController@getById');
    $router->put('/{id}/temporary', 'ChatAreaController@setTemporaryFalse');
});

// Chat routes
$router->group(['prefix' => 'chats'], function () use ($router) {
    $router->post('/', 'ChatController@create');
    $router->get('/', 'ChatController@getAll');
    $router->get('/{id}', 'ChatController@getById');
    $router->delete('/{id}', 'ChatController@delete');
    $router->put('/{id}/temporary', 'ChatController@setTemporaryFalse');
    $router->get('/broadcast/{spaceId}', 'ChatController@getBroadcastBySpaces');
    $router->get('/chatarea/{chatAreaId}', 'ChatController@getByChatArea');
    $router->get('/personal/{userId1}/{userId2}', 'ChatController@findPersonalChat');
});

// Chats Member routes
$router->group(['prefix' => 'chat_members'], function () use ($router) {
    $router->get('/chat/{chatId}', 'ChatsMemberController@membersByChat');    
    $router->get('/user/{userId}', 'ChatsMemberController@membersById');    
    $router->get('/pesonal/{userId}', 'ChatsMemberController@getChatByUser');
    $router->get('/{chatId}/{userId}', 'ChatsMemberController@getByChatAndUserId');
    $router->post('/', 'ChatsMemberController@store');                    
    $router->delete('{id}', 'ChatsMemberController@destroy');             
});

// Chat Message routes
$router->group(['prefix' => 'chat_messages'], function () use ($router) {
    $router->get('/chat/{chatId}', 'ChatMessageController@getByChat');    
    $router->post('/', 'ChatMessageController@store');       
    $router->delete('/chat/{chatId}', 'ChatMessageController@destroy');
});


