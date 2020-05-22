<?php 
    require_once('../comicVine.php');
    require_once('../mongo.php');
    require_once('../init_twig.php');
    require_once '../init_logger.php';

    $template = $_GET['template'] ?: 'issue_action.html';
    $issue_formatted = ['id'        =>  $_GET['id'],
                        'name'      =>  $_GET['name'],
                        'icon_url'  =>  $_GET['icon_url'],
                        'number'    =>  $_GET['number']
                    ];
    

    $twig->load($template)->display($issue_formatted);          
 ?>