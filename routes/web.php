<?php

use App\Http\Controllers\CodeController;
use App\Http\Controllers\BetCompanyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\DropletController;
use DigitalOceanV2\Client;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\System\SSH\Agent;

// Homepage Route
Route::get('/testercode', function () {


    $newDomainName ="test.com";
    $oldDomainName = "oldtest.com";
    $token = Config::get('values.DIGITALOCEAN_ACCESS_TOKEN');
    $client = new Client();
    $client->authenticate($token);
    $domainRecord = $client->domainRecord();
    $dropletClient = $client->droplet();
    $WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM = Config::get('values.WHICH_MAIL_FOR_SSH_CONNECT_PROBLEM');
    $public_key_root = Config::get('values.PUBLIC_KEY_ROOT');
    $private_key_root = Config::get('values.PRIVATE_KEY_ROOT');
    $droplet = $client->droplet();
    $droplets = $droplet->getAll();
    $connection = ssh2_connect("206.189.61.106", 22, array('hostkey' => 'ssh-rsa'));
    if (!ssh2_auth_pubkey_file(
        $connection,
        'root',
        $public_key_root,
        $private_key_root,
        'secret'

    )) {
       echo 5;
        exit();
    }
    $document_root ='1xbet-html-page';

    $execute_code = 'echo "<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    ServerName ' . $newDomainName . '
    ServerAlias www.' . $newDomainName . '
    DocumentRoot /var/www/'.$document_root.'
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>" >> /etc/apache2/sites-available/' . $newDomainName . '.conf';


    ssh2_exec($connection,$execute_code);
    ssh2_exec($connection,'a2ensite ' . $newDomainName . '.conf');
    ssh2_exec($connection,'systemctl restart apache2');

    //SSL CONFİG
    ssh2_exec($connection,'certbot --apache -d ' . $newDomainName . ' -d www.' . $oldDomainName);
    sleep(15);
    ssh2_exec($connection,'2');
});

Route::get('/get_new_sentence', 'App\Http\Controllers\RewriterController@get_new_sentence')->name('get_new_sentence');
Route::get('/', 'App\Http\Controllers\WelcomeController@welcome')->name('welcome');
Route::get('/terms', 'App\Http\Controllers\TermsController@terms')->name('terms');
Route::get('/un_used_domain_create', 'App\Http\Controllers\DomainController@un_used_domain_create')->name('un_used_domain_create');
Route::post('/un_used_domain_delete', 'App\Http\Controllers\DomainController@un_used_destroy')->name('un_used_domain_delete');
Route::get('/un_used_domain_index', 'App\Http\Controllers\DomainController@un_used_domain_index')->name('un_used_domain_index');
Route::post('/un_used_domain_store', 'App\Http\Controllers\DomainController@un_used_domain_store')->name('un_used_domain_store');


Route::resource('domains', DomainController::class);
Route::resource('codes', CodeController::class);
Route::resource('bet_companies', BetCompanyController::class);


// Homepage Route
Route::group(['middleware' => ['web', 'checkblocked']], function () {
    //ADD NEW DROPLET
    Route::get('/add_new_droplet', 'App\Http\Controllers\DropletController@add_new_droplet')->name('add_new_droplet');
    //DELETE NEW DROPLET
    Route::get('/delete_droplet', 'App\Http\Controllers\DropletController@delete_droplet')->name('delete_droplet');

    // THİS APİ CAN ADD NEW DNS RECORDS AND APACHE CONFİG
    Route::get('/add_new_domain_server_records', 'App\Http\Controllers\DomainController@add_new_domain_server_records')->name('add_new_domain_server_records');
    // THİS APİ CAN ADD OLD DNS RECORDS AND REDİRECT SERVER APACHE CONFİG
    Route::get('/old_domain_move_redirect_server', 'App\Http\Controllers\DomainController@old_domain_move_redirect_server')->name('old_domain_move_redirect_server');
    Route::get('/banlanmalogu', 'App\Http\Controllers\DomainController@banlanmalogu')->name('banlanmalogu');
});































































// Authentication Routes
Auth::routes();

// Public Routes
Route::group(['middleware' => ['web', 'activity', 'checkblocked']], function () {

    // Activation Routes
    Route::get('/activate', ['as' => 'activate', 'uses' => 'App\Http\Controllers\Auth\ActivateController@initial']);

    Route::get('/activate/{token}', ['as' => 'authenticated.activate', 'uses' => 'App\Http\Controllers\Auth\ActivateController@activate']);
    Route::get('/activation', ['as' => 'authenticated.activation-resend', 'uses' => 'App\Http\Controllers\Auth\ActivateController@resend']);
    Route::get('/exceeded', ['as' => 'exceeded', 'uses' => 'App\Http\Controllers\Auth\ActivateController@exceeded']);

    // Socialite Register Routes
    Route::get('/social/redirect/{provider}', ['as' => 'social.redirect', 'uses' => 'App\Http\Controllers\Auth\SocialController@getSocialRedirect']);
    Route::get('/social/handle/{provider}', ['as' => 'social.handle', 'uses' => 'App\Http\Controllers\Auth\SocialController@getSocialHandle']);

    // Route to for user to reactivate their user deleted account.
    Route::get('/re-activate/{token}', ['as' => 'user.reactivate', 'uses' => 'App\Http\Controllers\RestoreUserController@userReActivate']);
});

// Registered and Activated User Routes
Route::group(['middleware' => ['auth', 'activated', 'activity', 'checkblocked']], function () {

    // Activation Routes
    Route::get('/activation-required', ['uses' => 'App\Http\Controllers\Auth\ActivateController@activationRequired'])->name('activation-required');
    Route::get('/logout', ['uses' => 'App\Http\Controllers\Auth\LoginController@logout'])->name('logout');
});

// Registered and Activated User Routes
Route::group(['middleware' => ['auth', 'activated', 'activity', 'twostep', 'checkblocked']], function () {

    //  Homepage Route - Redirect based on user role is in controller.
    Route::get('/home', ['as' => 'public.home',   'uses' => 'App\Http\Controllers\UserController@index']);





    // Show users profile - viewable by other users.
    Route::get('profile/{username}', [
        'as'   => '{username}',
        'uses' => 'App\Http\Controllers\ProfilesController@show',
    ]);
});

// Registered, activated, and is current user routes.
Route::group(['middleware' => ['auth', 'activated', 'currentUser', 'activity', 'twostep', 'checkblocked']], function () {



    // User Profile and Account Routes
    Route::resource(
        'profile',
        \App\Http\Controllers\ProfilesController::class,
        [
            'only' => [
                'show',
                'edit',
                'update',
                'create',
            ],
        ]
    );
    Route::put('profile/{username}/updateUserAccount', [
        'as'   => '{username}',
        'uses' => 'App\Http\Controllers\ProfilesController@updateUserAccount',
    ]);
    Route::put('profile/{username}/updateUserPassword', [
        'as'   => '{username}',
        'uses' => 'App\Http\Controllers\ProfilesController@updateUserPassword',
    ]);
    Route::delete('profile/{username}/deleteUserAccount', [
        'as'   => '{username}',
        'uses' => 'App\Http\Controllers\ProfilesController@deleteUserAccount',
    ]);

    // Route to show user avatar
    Route::get('images/profile/{id}/avatar/{image}', [
        'uses' => 'App\Http\Controllers\ProfilesController@userProfileAvatar',
    ]);

    // Route to upload user avatar.
    Route::post('avatar/upload', ['as' => 'avatar.upload', 'uses' => 'App\Http\Controllers\ProfilesController@upload']);
});

// Registered, activated, and is admin routes.
Route::group(['middleware' => ['auth', 'activated', 'role:admin', 'activity', 'twostep', 'checkblocked']], function () {
    Route::resource('/users/deleted', \App\Http\Controllers\SoftDeletesController::class, [
        'only' => [
            'index', 'show', 'update', 'destroy',
        ],
    ]);

    Route::resource('users', \App\Http\Controllers\UsersManagementController::class, [
        'names' => [
            'index'   => 'users',
            'destroy' => 'user.destroy',
        ],
        'except' => [
            'deleted',
        ],
    ]);
    Route::post('search-users', 'App\Http\Controllers\UsersManagementController@search')->name('search-users');

    Route::resource('themes', \App\Http\Controllers\ThemesManagementController::class, [
        'names' => [
            'index'   => 'themes',
            'destroy' => 'themes.destroy',
        ],
    ]);

    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
    Route::get('routes', 'App\Http\Controllers\AdminDetailsController@listRoutes');
    Route::get('active-users', 'App\Http\Controllers\AdminDetailsController@activeUsers');
});

Route::redirect('/php', '/phpinfo', 301);
