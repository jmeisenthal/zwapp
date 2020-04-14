<?php 
    require_once('../comicVine.php');
    require_once('../mongo.php');
    require_once('../init_twig.php');
    require_once '../init_logger.php';

    $volume_id = $_GET['volume'];
    $volume = ZwappMongo\Collection::getVolumes()->getMap()[$volume_id];

    $template = $_GET['template'] ?: 'dial.html';
    $volume_formatted = [];
    
    $volume_formatted['id'] = $volume->id; 
    $volume_formatted['name'] = $volume->name;
    // $volume_formatted['icon_url'] = $volume->icon_url;
    $volume_formatted['first'] = $volume->first_issue->issue_number;
    $volume_formatted['last'] = $volume->last_issue->issue_number;
    // $volume_formatted['type'] = "volume";

    $twig->load($template)->display($volume_formatted);          
 ?>