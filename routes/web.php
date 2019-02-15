<?php

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

Auth::routes();

Route::view('/', 'welcome');

Route::get('/create', function () {
    $user = Auth::user();
    $token = $user->createToken('My t', ['edit'])->accessToken;
    dump($token);
    return $user;
});

Route::get('/callback', function (\Illuminate\Http\Request $request) {
    dd($request);
    if (isset($_REQUEST['code']) && $_REQUEST['code']) {
        $ch = curl_init();
        $url = 'http://passport.local/oauth/token';

        $params = array(
            'grant_type' => 'authorization_code',
            'client_id' => '1',
            'client_secret' => 'RcNyQsxiRTeKWudvNNZRUlPChUWQTY7I60KScZEF',
            'redirect_uri' => 'http://passport.local/callback',
            'code' => $_REQUEST['code']
        );

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $params_string = '';

        if (is_array($params) && count($params)) {
            foreach ($params as $key => $value) {
                $params_string .= $key . '=' . $value . '&';
            }

            rtrim($params_string, '&');

            curl_setopt($ch, CURLOPT_POST, count($params));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        }

        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result);

        // check if the response includes access_token
        if (isset($response->access_token) && $response->access_token) {
            // you would like to store the access_token in the session though...
            $access_token = $response->access_token;

            // use above token to make further api calls in this session or until the access token expires
            $ch = curl_init();
            $url = 'http://passport.local/api/user/get';
            dump($access_token);
            $header = array(
                'Authorization: Bearer ' . $access_token
            );
            $query = http_build_query(array('uid' => '6'));

            curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            $result = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($result);
            var_dump($result);
        } else {
            // for some reason, the access_token was not available
            // debugging goes here
        }
    }
});

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/enabImlGrant', function () {
    $query = http_build_query([
        'client_id' => '1',
        'redirect_uri' => 'http://passport.local/callback',
        'response_type' => 'token',
        'scope' => 'job',
    ]);

    return redirect('http://passport.local/oauth/authorize?'.$query);
});
