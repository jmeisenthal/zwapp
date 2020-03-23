
<?php
	require_once('php/init_twig.php');

    // Get IP address of user
    function getUserIP() {    
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
        return null;

      // if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      //   $ip = $_SERVER['HTTP_CLIENT_IP'];
      // } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      //   $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      // } else {
      //   $ip = $_SERVER['REMOTE_ADDR'];
      // }
      // return $ip;
    }

    // Check if cookie exists. If not then set new cookie. Return its value
    function getUserCookie() {
      if(isset($_COOKIE['visited'])) {
        // cookie is already set
      } else {
        $ip = getUserIP();
        $expire = time()+60*60*24*30; // expiration after 30 days
        setcookie("visited", $ip, $expire); 
      }
      return $_COOKIE['visited'];
    }

    $client = new MongoDB\Client("mongodb://localhost:27017");
    $visitors = $client->zwapp_admin->visitors;

    $ip = getUserCookie();

    $visited_doc = $visitors->findOne(['visited' => $ip]);
    if (is_null($visited_doc)) {
        $visitors->insertOne(['visited' => $ip, 'count' => 1]);
    } else {
        $count = $visited_doc->count + 1;
        $visitors->updateOne(['visited' => $ip], ['$set' => ['count' => $count]]);
    }

	$twig->load('index.html')->display(array('application'=>'Zwapp!'));
?>


