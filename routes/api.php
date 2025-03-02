<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('register', 'JWTAuthController@register');
    Route::post('login', 'JWTAuthController@login');
    Route::post('logout', 'JWTAuthController@logout');
    Route::post('refresh', 'JWTAuthController@refresh');
    Route::get('profile', 'JWTAuthController@profile');

});


Route::group([
    'middleware' => 'api',
    'prefix' => 'shan',
], function($router){
    Route::get('testGet','testController@returnRequest');
    Route::get('layoutTest', 'LayoutController@layoutTest');
    Route::post('testPost', 'testController@returnPost');
    Route::post('fileUpload', 'FileUploadController@recieveFile')->name('fileUpload');
    Route::post('/imageUploadCk', 'ckUploadController@recieveFile')->name('imageUploadCk');
    Route::get('getLayout', 'cardInstanceController@getLayoutById');
    Route::post('/saveCardOnly', 'cardInstanceController@saveCardOnly')->name('saveCardOnly');
    Route::post('/saveCardParameters','cardInstanceController@saveCardParameters')->name('saveCardParameters');
    Route::post('/saveCardContent','cardInstanceController@saveCardContent')->name('saveCardContent');
    Route::get('/getCardDataById', 'cardInstanceController@getCardDataById')->name('getCardDataById');
    Route::post('/createLayoutNoBlanks', 'LayoutController@createNewLayoutNoBlanks')->name('newlayoutNoBlanks');
    Route::get('getMySpaces', 'LayoutController@getMySpaces');
    Route::get('getMyDeletedSpaces', 'LayoutController@getMyDeletedSpaces')->name('getMyDeletedSpaces');
    Route::get('undeleteThisSpace', 'LayoutController@undeleteThisSpace')->name('undeleteThisSpace');
    Route::get('orgList', 'OrgController@getOrgList');
    Route::get('orgUsers', 'OrgController@getOrgUsers');
    Route::get('getAvailableMembers', 'OrgController@getAvailableUsersInOrg');
//    Route::get('availableOrgUsers', 'OrgController@getAvailableOrgUsers');
    Route::get('availableUsers', 'OrgController@getAvailableUsers');
    Route::get('orgLayouts', 'LayoutController@getOrgLayouts');
    Route::get('allUsers', 'OrgController@getAllUsers');
    Route::post('newOrg', 'OrgController@newOrg');
//    Route::get('orgGroups', 'GroupsController@getOrgGroups');
    Route::get('layoutPerms', 'LayoutController@getLayoutPerms');
    Route::post('setLayoutPerms', 'LayoutController@setLayoutPerms');
    Route::get('groupMembers', 'GroupsController@getGroupMembers');
    Route::get('orgGroups', 'GroupsController@getOrgGroups');
    Route::post('removePerm', 'LayoutController@removePerm');
    Route::post('removeUserFromGroup', 'GroupsController@removeUserFromGroup');
    Route::post('addUserToGroup', 'GroupsController@addUserToGroup');
    Route::post('addAccess', 'LayoutController@addAccessForGroupToLayout');
    Route::post('setupNewUser', 'userController@setupNewUser');
    Route::post('createUser', 'userController@createUser');
    Route::post('updatePassword', 'userController@updatePassword');
    Route::post('addUserToOrg','userController@addUserToOrg' );
    Route::get('removeUserFromOrg', 'GroupsController@removeUserFromOrg');
    Route::get('getLinks', 'linkController@getLinksByCardId');
    Route::post('createNewLink', 'linkController@createNewLink');
    Route::post('resizeCard', 'cardInstanceController@resizeCard');
    Route::get('publishOrg', 'LayoutController@publishOrg');
    Route::get('userOrgPerms','OrgController@userOrgPerms' );
    Route::get('deleteLayout', 'LayoutController@deleteLayout');
    Route::get('deleteLink', 'linkController@deleteLink');
    Route::get('rmvlay', 'LayoutController@removeCardFromLayout');
    Route::get('deleteCard', 'LayoutController@deleteC  ard');
    Route::get('cardList', 'cardInstanceController@getOrgCards');
    Route::post('cardInsert', 'cardInstanceController@cardInsert');
    Route::get('documentDefaults', 'ArchiveController@getDocumentDefaults');
    Route::get('getFile','FileUploadController@sendFile')->name('getFile');
    Route::get('removeUploadedFile', 'FileUploadController@removeUploadedFile')->name('removeUploadedFile');
    Route::get('getLayoutParams','LayoutController@getLayoutParams')->name('getLayoutParams');
    Route::post('updateLayout', 'LayoutController@updateLayout')->name('updateLayout');
    Route::post('updateCardLinks','linkController@updateCardLinks')->name('updateCardLinks');
    Route::get('availableTemplates', 'LayoutController@getAvailableTemplates')->name('getAvailableTemplates');
    Route::post('cloneTemplate', 'LayoutController@cloneTemplate')->name('cloneTemplate');
    Route::post('addCurrentLayout', 'linkController@addCurrentLayout')->name('addCurrentLayout');
    Route::get('solrSimpleQuery', 'solrSearchController@simpleQuery')->name('solrSimpleQuery');
    Route::post('updateCardName', 'cardInstanceController@updateCardName');
    Route::post('updateCardTitle', 'cardInstanceController@updateCardTitle')->name('updateCardTitle');
    Route::get('getOrgHome','OrgController@getOrgHome')->name('getOrgHome');
    Route::get('layoutInfo','LayoutController@getLayoutInfoById')->name('getLayoutInfo');
    Route::get('getLinkLabel', 'linkController@getLinkLabel');
    Route::get('userInOrg', 'OrgController@userInOrg')->name('userInOrg');
    Route::get('setOrgRestrict', 'OrgController@setOrgRestrict')->name('setOrgRestrict');
    Route::get('saveRestrictedRegistrant', 'OrgController@saveRestrictedRegistrant')->name('saveRestrictedRegistrant');
    Route::get('getAllowedRegistrants', 'OrgController@getAllowedRegistrants')->name('getAllowedRegistrants');
    Route::get('updateAllowedRegistrant', 'OrgController@updateAllowedRegistrant')->name('updateAllowedRegistrant');
    Route::get('deleteAllowedRegistrant', 'OrgController@deleteAllowedRegistrant')->name('deleteAllowedRegistrant');
    Route::get("allowOpenRegistration", 'OrgController@allowOpenRegistration')->name('allowOpenRegistration');
    Route::get('registrationPermitted', 'OrgController@registrationPermitted')->name('registrationPermitted');
    Route::post('addUpdateLink', 'linkController@addUpdateLink')->name('addUpdateLink');
    Route::get('testOrgRestrict', 'OrgController@testOrgRestrict')->name('testOrgRestrict');
    Route::post('saveCardAndConfiguration','cardInstanceController@saveCardAndConfiguration')->name('saveCardAndConfiguration');
    Route::post('addNewLink', 'linkController@addNewLink')->name('addNewLink');
    Route::get('countMySpaces','LayoutController@countMySpaces')->name('countMySpaces');
    Route::get('getMySpacesPaged','LayoutController@getMySpacesPaged')->name('getMySpacesPaged');
    Route::get('getLinkInfo','linkController@getLinkInfo')->name('getLinkInfo');

});

