<?php

use App\Http\Controllers\CodeController;
use App\Http\Controllers\BetCompanyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\ContentController;

use App\Models\BannedList;
use App\Models\Code;
use App\Models\Content;
use App\Models\Domain;
use App\Models\GitDomain;
use App\Models\Log;
use App\Models\ServerSetting;
use App\Models\Website;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use phpseclib3\Net\SSH2;

Route::get('/getallwebsites', function () {
    $website = Website::all()->first();
    $part = "/wp-json/wp/v2/posts/";
    $category_part = "/wp-json/wp/v2/categories";
    $post_id = 1;
    $rest_api_link = $website->link . $part . $post_id;
    $rest_api_link_category_part = $website->link . $category_part;
    $categories = null;

    // GET CATEGORIES

    $curlSession = curl_init();
    curl_setopt($curlSession, CURLOPT_URL, $rest_api_link_category_part);
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
    $jsonData = json_decode(curl_exec($curlSession));
    curl_close($curlSession);
    if (!isset($jsonData->data->status))
        $categories = $jsonData;

    //

    $curlSession = curl_init();
    curl_setopt($curlSession, CURLOPT_URL, $rest_api_link);
    curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
    $jsonData = json_decode(curl_exec($curlSession));

    curl_close($curlSession);
    if (!isset($jsonData->data->status)) {
        $link = $jsonData->slug;
        $title = $jsonData->title->rendered;
        $wp_content = $jsonData->content->rendered;
        $description = $jsonData->excerpt->rendered;
        $category  = '';
    }
    if (isset($jsonData->categories[0])) {
        $cat_id = $jsonData->categories[0];
        foreach ($categories as $key => $value) {
            if ($value->id == $cat_id)
                $category = $value->name;
        }
    }
    $content = new Content();
    $content->first_link = $link;
    $content->first_title = $title;
    $content->first_description = $description;
    $content->first_content = $wp_content;
    $content->first_category = $category;

    $content->rewriter_title = $title;
    $content->rewriter_description = $description;
    $content->rewriter_content = $wp_content;

    $content->status = 1;


    $content->save();
});


Route::get('/server_free', function () {
    $server_settings = ServerSetting::all()->first();
    $server_settings->is_server_busy = false;
    $server_settings->website_picker_busy = false;
    $server_settings->website_picker_second_busy = false;
    $server_settings->new_domain_get_controller = false;
    $server_settings->check_domain_controller = false;
    $server_settings->banned_domain_get_controller = false;

    $server_settings->save();
});



Route::get('/content/{first_link}', function ($first_link) {
    dd(Content::where('first_link', $first_link)->get());
});


// Homepage Route
Route::get('/cleaner', function () {
    /*     GitDomain::truncate();
    Domain::truncate();
    BannedList::truncate();
    Content::truncate();
    Website::truncate();
 */
});

Route::get('/change_website_status/{id}', function ($id) {
    $website = Website::where('id', $id)->get()->first();
    $website->status = -1;
    $website->save();
});

Route::get('/delete_website_content/{id}', function ($id) {

    $contents = Content::where('website_id', $id)->get();
    foreach ($contents as $key => $value) {
        $value->delete();
    }
});
Route::get('/clean_content_websitesi', function () {
    $website = Website::where('status',-1)->get();
    foreach ($website as $key => $value) {
        $value->delete();
    }
});

Route::get('/wrong_content_delete', function () {
    dd(5);
    for ($i = 1093; $i < 1433; $i++) {
        $content = Content::where('id', $i)->get()->first();
        $content->delete();
    }
});


Route::get('/testerrrr', function () {

    $TR_SERVER_IP = Config::get('values.TR_SERVER_IP');
    $TR_SERVER_SSH_USERNAME = Config::get('values.TR_SERVER_SSH_USERNAME');
    $TR_SERVER_PASSWORD = Config::get('values.TR_SERVER_PASSWORD');

    $ssh = new SSH2($TR_SERVER_IP);
    if (!$ssh->login($TR_SERVER_SSH_USERNAME, $TR_SERVER_PASSWORD)) {


        dd("hata");
    }

    $link = "http://rosaluxspba.org/wp-json/wp/v2/categories";
    $command = 'curl -s -H "Proxy-Connection: keep-alive"  -H "Cache-Control: max-age=0"   -H "Upgrade-Insecure-Requests: 1" -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36" -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"  -H "Accept-Language: tr-TR,tr;q=0.9,tr;q=0.8" ' . $link;
    $html = $ssh->exec($command);
    $jsonData = json_decode($html);
    dd($jsonData);
});

Route::get('/custom_get_content/{id}', function ($id) {

    $TR_SERVER_IP = Config::get('values.TR_SERVER_IP');
    $TR_SERVER_SSH_USERNAME = Config::get('values.TR_SERVER_SSH_USERNAME');
    $TR_SERVER_PASSWORD = Config::get('values.TR_SERVER_PASSWORD');

    $part = "/wp-json/wp/v2/posts/";
    $category_part = "/wp-json/wp/v2/categories";
   

    $ssh = new SSH2($TR_SERVER_IP);
    if (!$ssh->login($TR_SERVER_SSH_USERNAME, $TR_SERVER_PASSWORD)) {


        return 0;
    }


    $website = Website::where('id', $id)->get()->first();

 

    $log = new Log();
    $log->type = 0;
    $log->title = "Başarılı";
    $log->which_worker = "websitePickerSecond";
    $log->description = $website->link . " içerikleri çekilmeye başlandı.";


    try {
        $timer = 0;
        for ($i = 1; $i < 50000; $i++) {



            $post_id = $i;
            $rest_api_link = $website->link . $part . $post_id;
            $command = 'curl -s -H "Proxy-Connection: keep-alive"  -H "Cache-Control: max-age=0"   -H "Upgrade-Insecure-Requests: 1" -H "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36" -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"  -H "Accept-Language: tr-TR,tr;q=0.9,tr;q=0.8" ' . $rest_api_link;
            $html = $ssh->exec($command);
            $jsonData = json_decode($html);

            if ($timer == 500) {
                //break;
            }
            if (!isset($jsonData->data->status)) {
                $save = true;
                $link = '';
                try {
                    $link = $jsonData->slug;
                    $save = true;
                } catch (\Throwable $th) {
                    $save = false;
                }


                $description = '';
                if ($save) {
                    $save = true;
                    $description = $jsonData->excerpt->rendered;
                } else {
                    $save = false;
                }


                $title = '';
                if ($save) {
                    $save = true;
                    $title =  $jsonData->title->rendered;
                } else {
                    $save = false;
                }

                $wp_content = '';
                if ($save) {
                    $save = true;
                    $wp_content  = $jsonData->content->rendered;
                } else {
                    $save = false;
                }

                if ($save) {
                    $content = new Content();
                    $content->first_link = $link;
                    $content->first_title = $title;
                    $content->first_description = $description;
                    $content->first_content = $wp_content;
                    $content->first_category = '';
                    $content->rewriter_title = $title;
                    $content->rewriter_description =  $description;
                    $content->website_id =  $website->id;
                    $content->save();
                    $timer == 0;
                }
            }
            $timer++;
        }

        $log = new Log();
        $log->type = 0;
        $log->title = "Başarılı";
        $log->which_worker = "websitePickerSecond";
        $log->description = $website->link . " içerikleri çekildi işlem tamamlandı.";
        $contents_c = Content::where('website_id', $website->id)->get();
        if (count($contents_c) > 0)
            $website->status = 1;
        else
            $website->status = -1;

        $website->save();
     
    } catch (\Throwable $th) {

        $log = new Log();
        $log->type = -1;
        $log->title = "Hata";
        $log->which_worker = "websitePickerSecond";
        $log->description = $website->link . " içerikleri çekerken hata meydana geldi. Hata: " . "$th";
        $website->status = -1;
        $website->save();
        $log->save();
        //Eski kayıtlarıda siliver 
        //Content::where("website_id",$website->id)->delete();
      
    }
});



Route::get('/ban/{newDomainName}', function ($newDomainName) {
    $domain = Domain::where('name', $newDomainName)->get()->first();
    if ($domain) {
        $domain->status = 3;
        $domain->domain_status = 1;
        $domain->save();
    }
});

Route::get('/', 'App\Http\Controllers\WelcomeController@welcome')->name('welcome');
Route::get('/terms', 'App\Http\Controllers\TermsController@terms')->name('terms');
Route::get('/un_used_domain_create', 'App\Http\Controllers\DomainController@un_used_domain_create')->name('un_used_domain_create');
Route::post('/un_used_domain_delete', 'App\Http\Controllers\DomainController@un_used_destroy')->name('un_used_domain_delete');
Route::get('/un_used_domain_index', 'App\Http\Controllers\DomainController@un_used_domain_index')->name('un_used_domain_index');
Route::post('/un_used_domain_store', 'App\Http\Controllers\DomainController@un_used_domain_store')->name('un_used_domain_store');
Route::get('/movable_and_used_domain_index', 'App\Http\Controllers\DomainController@movable_and_used_domain_index')->name('movable_and_used_domain_index');


Route::resource('domains', DomainController::class);
Route::resource('codes', CodeController::class);
Route::resource('bet_companies', BetCompanyController::class);
Route::resource('websites', WebsiteController::class);
Route::resource('contents', ContentController::class);

Route::post('/rewriter', 'App\Http\Controllers\ContentController@rewriter')->name('rewriter');


// Homepage Route
Route::group(['middleware' => ['web', 'checkblocked']], function () {
    //ADD NEW DROPLET
    Route::get('/add_new_droplet', 'App\Http\Controllers\DropletController@add_new_droplet')->name('add_new_droplet');
    //DELETE NEW DROPLET
    Route::get('/delete_droplet', 'App\Http\Controllers\DropletController@delete_droplet')->name('delete_droplet');

    // GİT
    Route::get('/add_new_git_domain', 'App\Http\Controllers\GitController@add_new_git_domain')->name('add_new_git_domain');
    Route::get('/delete_git_domain', 'App\Http\Controllers\GitController@delete_git_domain')->name('delete_git_domain');




    // ACTIONS

    Route::get('/new_add_and_old_delete_request', 'App\Http\Controllers\ActionController@new_add_and_old_delete_request')->name('new_add_and_old_delete_request');
    Route::get('/new_add_request', 'App\Http\Controllers\ActionController@new_add_request')->name('new_add_request');



    // THİS APİ CAN ADD NEW DNS RECORDS AND APACHE CONFİG
    Route::get('/add_new_domain_server_records', 'App\Http\Controllers\DomainController@add_new_domain_server_records')->name('add_new_domain_server_records');
    // THİS APİ CAN ADD OLD DNS RECORDS AND REDİRECT SERVER APACHE CONFİG
    Route::get('/old_domain_move_redirect_server', 'App\Http\Controllers\DomainController@old_domain_move_redirect_server')->name('old_domain_move_redirect_server');

    Route::get('/error_new_domain_server_records_delete', 'App\Http\Controllers\DomainController@error_new_domain_server_records_delete')->name('error_new_domain_server_records_delete');

    Route::get('/error_old_domain_move_redirect_server', 'App\Http\Controllers\DomainController@error_old_domain_move_redirect_server')->name('error_old_domain_move_redirect_server');

    Route::get('/banlanmalogu', 'App\Http\Controllers\DomainController@banlanmalogu')->name('banlanmalogu');
    Route::get('/server_setting', 'App\Http\Controllers\ServerSettingController@index')->name('server_setting');

    // LOGGİNG
    Route::get('/logging', 'App\Http\Controllers\LogController@index')->name('logging');
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
