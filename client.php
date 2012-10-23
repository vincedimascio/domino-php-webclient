#!/opt/bin/php
<?php
/*

 basic php agent for connecting to domino, authenticating
 getting cookie, then posting data to an agent and getting 
 a response.
 
*/

// globals
$base_url = "https://myserver.com";
$webdbname = "/path/to/file/db.nsf";
$username = "jsmith";
$password = "pa66word";
$dominoCookie = "";
$authCookieName = "DomAuthSessId";

function getCookie( $cookieName ) {
    global $base_url;
    global $webdbname;
    global $password;
    global $dominoCookie;
    global $username;
   
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,  $base_url . $webdbname . "?login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    $data = array(
       'username' => $username,
       'password' => $password
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($ch);
    curl_close($ch);
   
    preg_match('/^Set-Cookie: ' . $cookieName . '=(.*?);/m', $output, $myCookieName);
    $cookie = $myCookieName[1];
   
    return $cookie;
}


function postToDb( $url, $postData ) {
    global $base_url;
    global $webdbname;
    global $password;
    global $dominoCookie;
    global $authCookieName;
    global $username;
       
    // make sure we have a cookie
    if (  strlen($dominoCookie) == 0 ) {
        $dominoCookie = getCookie( $authCookieName );
    }
   
    // connect and post
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,  $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_COOKIE,  $authCookieName .'='. $dominoCookie);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData );
    $output = curl_exec($ch);
    curl_close($ch);   
   
    return $output;
}

$xml = '<data><field1>red</field1><field2>blue</field2>></data>';
$responseData = postToDb( $base_url . $webdbname . "/XMLHandlerAgent?OpenAgent", $xml );
$doc = new SimpleXmlElement($responseData, LIBXML_NOCDATA);
$status = $doc->status;
$command = $doc->command;
$file = $doc->file;

if ( $status ==  "Error" ) {
    // server responded with error status 
    echo "Error" . $doc->message;
 
} elseif ( $status == "OK" ) {
    // server responded with OK status

   
} else {
    // server responded with some unknown or missing status -- handle
    echo "Unhandled";
   
}


?>