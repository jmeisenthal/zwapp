<?php 
    require_once('../comicVine.php');
    require_once('../mongo.php');
    require_once('../init_twig.php');
    require_once '../init_logger.php';

    $volume_id = $_GET['volume'];
    $issue_number = $_GET['issue_number'];
    $issue = ZwappMongo\Collection::getVolumeIssue($volume_id, $issue_number);

    $template = $_GET['template'] ?: 'dial__content.html';
    $issue_formatted = [];
    
    $issue_formatted['id'] = $issue->id; 
    $issue_formatted['name'] = $issue->name;
    $issue_formatted['number'] = $issue_number;
    $issue_formatted['icon_url'] = $issue->icon_url;
    $issue_formatted['type'] = "issue";
    // print_r("Name: ".($issue->name));
    // var_dump($issue);

    $twig->load($template)->display($issue_formatted);
 ?>