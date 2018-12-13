<?php

use Illuminate\Http\Request;

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
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['api'])->group(function () {
	// Vauth group ------------------------------------------------------------------------
	Route::group([
			'as' => 'vauth::', 
			'namespace' => 'VAuth', 
			'prefix' => 'vauth'
		], function () {

		// Authentication group ------------------------------------------------------------------------
		Route::group([
			'as' => 'auth::', 
			'namespace' => 'Auth', 
			'prefix' => 'auth'
		], function () {
            Route::post('signup', 'SignUpController@signup');
            Route::get('active/{code}', 'SignUpController@activeSignupCode');
            Route::post('password/forgot', 'PasswordController@forgotPassword');
            Route::post('password/change', 'PasswordController@updateForgotPassword');
        });

        // Facebook auth group ------------------------------------------------------------------------
		Route::group([
			'as' => 'fbauth::', 
			'namespace' => 'Fb\Auth', 
			'prefix' => 'fb/auth'
		], function () {
            Route::post('', 'FbAuthController@auth');
        });

        // Google auth group ------------------------------------------------------------------------
		Route::group([
			'as' => 'googleauth::', 
			'namespace' => 'Google\Auth', 
			'prefix' => 'google/auth'
		], function () {
            Route::post('', 'GoogleAuthController@auth');
        });

		// Device group ------------------------------------------------------------------------
        Route::group([
			'middleware' => 'auth:api'
		], function () {

            Route::group([
				'as' => 'device::', 
				'namespace' => 'Device', 
				'prefix' => 'device',
				'middleware' => [
					'vauth.passport.parse.token',
					'vauth.permission.verified'
				]
			], function () {
	            Route::post('register', 'DeviceController@registerDevice');
	        });

        });

        // Profule group ------------------------------------------------------------------------
        Route::group([
			'middleware' => 'auth:api'
		], function () {

            Route::group([
				'as' => 'profile::', 
				'namespace' => 'Profile', 
				'prefix' => 'profile',
				'middleware' => [
					'vauth.passport.parse.token',
					'vauth.permission.verified'
				]
			], function () {
	            Route::get('me', 'ProfileController@myProfile');
	        });

        });
	});

	// Adminstration group ------------------------------------------------------------------------
	Route::group([
		'as' => 'vauthadministrator::', 
		'namespace' => 'VAuthAdministrator', 
		'prefix' => 'vauthcenter', 
		'middleware' => 'auth:api'
	], function () {

		Route::group([
			'middleware' => [
				'vauth.passport.parse.token',
				'vauth.permission.verified',
				'vauth.permission.admin.access',
			]
		], function () {

			// User group ------------------------------------------------------------------------
			Route::group([
				'as' => 'user::', 
				'namespace' => 'User', 
				'prefix' => 'user',
				'middleware' => [
					'vauth.permission.require.all:user:view'
				]
			], function () {
	            Route::get('', 'UserController@users');
	            Route::patch('{userId}', 'UserController@updateUser')->middleware('vauth.permission.require.all:user:update');
	            Route::post('lock', 'UserController@lockUsers')->middleware('vauth.permission.require.all:user:lock');
	            Route::post('unlock', 'UserController@unlockUsers')->middleware('vauth.permission.require.all:user:unlock,user:view-deleted');
	            Route::post('delete', 'UserController@deleteUsers')->middleware('vauth.permission.require.all:user:delete');

	            // Device group ------------------------------------------------------------------------
	            Route::group([
					'as' => 'device::', 
					'namespace' => 'Device', 
					'prefix' => '{userId}/device'
				], function () {
		            Route::get('', 'UserDeviceController@devices');
		        });
	        });

			// Client group ------------------------------------------------------------------------
			Route::group([
				'as' => 'client::', 
				'namespace' => 'Client', 
				'prefix' => 'client',
				'middleware' => [
					'vauth.permission.require.all:client:view'
				]
			], function () {
				Route::get('{clientId}', 'ClientController@client');
	            Route::get('', 'ClientController@clients');
	            Route::post('', 'ClientController@createClient')->middleware('vauth.permission.require.all:client:create');
	            Route::patch('{clientScrubId}', 'ClientController@updateClient')->middleware('vauth.permission.require.all:client:update');
	            Route::post('{clientScrubId}/secret/reset', 'ClientController@resetSecret')->middleware('vauth.permission.require.all:client:regenerate-secret');
	            Route::post('lock', 'ClientController@lockClients')->middleware('vauth.permission.require.all:client:lock');
	            Route::post('unlock', 'ClientController@unlockClients')->middleware('vauth.permission.require.all:client:unlock,client:view-deleted');
	            Route::post('delete', 'ClientController@deleteClients')->middleware('vauth.permission.require.all:client:delete');

	            // User group ------------------------------------------------------------------------
	            Route::group([
				'as' => 'user::', 
				'namespace' => 'User', 
				'prefix' => '{clientScrubId}/user',
				'middleware' => [
					'vauth.permission.require.all:user:view'
				]
				], function () {
					Route::get('', 'ClientUserController@users');
				});

	            // Permission group ------------------------------------------------------------------------
	            Route::group([
				'as' => 'permission::', 
				'namespace' => 'Permission', 
				'prefix' => '{clientScrubId}/permission',
				], function () {
					Route::get('', 'ClientPermissionController@permissions');
					Route::post('', 'ClientPermissionController@createPermission')->middleware('vauth.permission.require.all:client:update');
					Route::patch('{permissionScrubId}', 'ClientPermissionController@updatePermission')->middleware('vauth.permission.require.all:client:update');
					Route::post('delete', 'ClientPermissionController@deletePermissions')->middleware('vauth.permission.require.all:client:update');

					// Permission Group group ------------------------------------------------------------------------
					Route::group([
					'prefix' => 'group/',
					], function () {
						Route::get('{parentScrubId}', 'ClientPermissionGroupController@groups');
						Route::post('', 'ClientPermissionGroupController@createGroup')->middleware('vauth.permission.require.all:client:update');
						Route::patch('{groupScrubId}', 'ClientPermissionGroupController@updateGroup')->middleware('vauth.permission.require.all:client:update');
						Route::post('lock', 'ClientPermissionGroupController@lockGroups')->middleware('vauth.permission.require.all:client:update');
						Route::post('unlock', 'ClientPermissionGroupController@unlockGroups')->middleware('vauth.permission.require.all:client:update,client:permission-view-deleted');
						Route::post('delete', 'ClientPermissionGroupController@deleteGroups')->middleware('vauth.permission.require.all:client:update,client:permission-view-deleted');

						// Permission inside Group group ------------------------------------------------------------------------
						Route::group([
						'prefix' => '{groupScrubId}/permission',
						], function () {
							Route::get('', 'ClientPermissionGroupController@groupPermissions');
							Route::post('', 'ClientPermissionGroupController@assignPermissionsToGroup');
							Route::post('delete', 'ClientPermissionGroupController@removePermissionsFromGroup');
							Route::get('available', 'ClientPermissionGroupController@groupAvailablePermissions');
						});

						// Users inside Group group ------------------------------------------------------------------------
						Route::group([
						'prefix' => '{groupScrubId}/user',
						], function () {
							Route::get('', 'ClientPermissionGroupUserController@groupUsers');
							Route::post('', 'ClientPermissionGroupUserController@assignUsersToGroup');
							Route::post('delete', 'ClientPermissionGroupUserController@removeUsersFromGroup');
							Route::get('available', 'ClientPermissionGroupUserController@groupAvailableUsers');
						});
					});
				});
	        });
        });
	});

	// Adminstration background group ------------------------------------------------------------------------
	Route::group([
			'as' => 'vauthbackground::', 
			'namespace' => 'VAuthBackground', 
			'prefix' => 'vauthprocess', 
			'middleware' => 'vauth.background.client'
		], function () {

		// Client group ------------------------------------------------------------------------
		Route::group([
				'as' => 'client::', 
				'namespace' => 'Client', 
				'prefix' => 'client/{clientId}',
			], function () {

			// User group ------------------------------------------------------------------------
			Route::group([
					'as' => 'user::', 
					'namespace' => 'User', 
					'prefix' => 'user/{userId}',
				], function () {
				// Permission group ------------------------------------------------------------------------
				Route::group([
						'as' => 'permission::', 
						'namespace' => 'Permission', 
						'prefix' => 'permission',
					], function () {
					Route::post('assignToGroups', 'ClientPermissionGroupController@assignUserIntoGroups');
				});
			});
		});
	});
});
