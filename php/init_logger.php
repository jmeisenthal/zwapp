<?php 
	$autoload = __DIR__ . '/../vendor/autoload.php';
	// require  "/Development/zwapp/public/vendor/autoload.php";
    require_once $autoload;

	// use Monolog\Logger;
	// use Monolog\Handler\StreamHandler;

	if (!is_null($logger)) {
		return;
	}

	$logger = new Monolog\Logger('my_logger');
	// $logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . "/../../log/zwapp.log", Monolog\Logger::DEBUG));
	$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . "/../../log/zwapp.log", Monolog\Logger::DEBUG));
	// $class = get_class($logger);
	// $methods = get_class_methods($class);
	// print_r("Methods for class $class: ");
	// print_r(array_values($methods));
	// $logger->warning("Test!");

	// error_log("test error_log");
	// $logger = $autoload;
	// $logger = get_class($logger);
?>