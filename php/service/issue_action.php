<?php 
    require_once('../comicVine.php');
    require_once('../mongo.php');
    require_once('../init_twig.php');
    require_once '../init_logger.php';

    $template = $_GET['template'] ?: 'issue_action.html';
    $issue_formatted = ['id'            =>  $_GET['id'],
                        'name'          =>  $_GET['name'],
                        'volume_name'   =>  $_GET['volume_name']
                    ];
    

    $twig->load($template)->display($issue_formatted);          
 ?>