<?php

require ('ext/Slim/Slim.php');
require ('ApiModel.php');
error_reporting(1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();
// http://domain.com/push_test
$app -> get('/push_test', function() {
    ApiModel::getInstance() -> push_test();
});
$app -> get('/push_test_android', function() {
    ApiModel::getInstance() -> push_test_android();
});

// http://domain.com/account/signup
$app -> post('/account/signup', function() {
    ApiModel::getInstance() -> signup();
});
// http://domain.com/account/login
$app -> post('/account/login', function() {
    ApiModel::getInstance() -> login();
});
// http://domain.com/logout
$app -> post('/account/logout', function() {
    ApiModel::getInstance() -> logout();
});
// http://domain.com/account/update_profile
$app -> post('/account/update_profile', function() {
    ApiModel::getInstance() -> update_profile();
});
// http://domain.com/chat/sendMessage
$app -> post('/chat/sendMessage', function() {
    ApiModel::getInstance() -> sendMessage();
});
// http://domain.com/account/update_email
$app -> post('/account/update_email', function() {
    ApiModel::getInstance() -> update_email();
});
// http://domain.com/account/update_password
$app -> post('/account/update_password', function() {
    ApiModel::getInstance() -> update_password();
});
// http://domain.com/account/delete_account
$app -> post('/account/delete_account', function() {
    ApiModel::getInstance() -> delete_account();
});
// http://domain.com/account/social_signup
$app -> post('/account/social_signup', function() {
    ApiModel::getInstance() -> social_signup();
});
// http://domain.com/account/social_login
$app -> post('/account/social_login', function() {
    ApiModel::getInstance() -> social_login();
});
// http://domain.com/get_profile
$app -> post('/get_profile', function() {
    ApiModel::getInstance() -> get_profile();
});


/***************************** Client **************************************************/
// http://domain.com/client/manage
$app -> post('/client/manage', function() {
    ApiModel::getInstance() -> manage();
});
// http://domain.com/client/get_candidate
$app -> post('/client/get_candidate', function() {
    ApiModel::getInstance() -> get_candidate();
});
// http://domain.com/client/get_matches
$app -> post('/client/get_matches', function() {
    ApiModel::getInstance() -> get_matches();
});
// http://domain.com/client/like
$app -> post('/client/like', function() {
    ApiModel::getInstance() -> client_like();
});
// http://domain.com/client/like
$app -> post('/client/dislike', function() {
    ApiModel::getInstance() -> client_dislike();
});

/***************************** Freelancer **************************************************/

// http://domain.com/freelancer/get_post
$app -> post('/freelancer/get_post', function() {
    ApiModel::getInstance() -> get_post();
});
// http://domain.com/freelancer/like
$app -> post('/freelancer/like', function() {
    ApiModel::getInstance() -> freelancer_like();
});
// http://domain.com/freelancer/dislike
$app -> post('/freelancer/dislike', function() {
    ApiModel::getInstance() -> freelancer_dislike();
});
// http://domain.com/client/get_matches
$app -> post('/freelancer/get_matches', function() {
    ApiModel::getInstance() -> get_freelancer_matches();
});

/*******************************************  end  *//////////////////////////////////////////////////////////
/*******************************************  end  *//////////////////////////////////////////////////////////
/*******************************************  end  *//////////////////////////////////////////////////////////


$app -> run();