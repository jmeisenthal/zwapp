
<?php
	require_once('php/init_twig.php');

    // Get IP address of user
    function getUserIP() {    
      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
        $ip = $_SERVER['REMOTE_ADDR'];
      }
      return $ip;
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


