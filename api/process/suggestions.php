<?php
    // All classes & managers
    spl_autoload_register(function($class) {
        if(strpos($class, "Manager") !== false)
            include __DIR__ . "../../manager/" . $class . ".php";
        else
            include __DIR__ . "../../class/" . $class . ".php";
    });


    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization");


    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
      /* Get suggestions */
      if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'get') {
          // GET SUGGESTIONS
          if(isset($_GET['search']) && !empty($_GET['search']) && isset($_GET['lang']) && !empty($_GET['lang'])) {
            $json = [];
            $search = strtolower(str_replace(' ', '%20', htmlspecialchars($_GET['search'])));
            if(strlen($search) > 1) {
              $lang = htmlspecialchars($_GET['lang']);
  
              $suggestions = SuggestionManager::getSuggestions($lang, $search);
              foreach ($suggestions as $suggestion) {
                  $json[$suggestion->word] = $suggestion->score;
              }
            }  
            echo json_encode($json);
          }
          else {
              http_response_code(400);
          }
      }
    }
    else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        /* Make a new search */
        if(isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'select')  {
            if(isset($_POST['search']) && !empty($_POST['search']) && isset($_POST['suggestion']) && !empty($_POST['suggestion']) && isset($_POST['search']) && !empty($_POST['search']))           {
                $search = strtolower(str_replace(' ', '%20', htmlspecialchars($_POST['search'])));
                $suggestion = str_replace(' ', '%20', htmlspecialchars($_POST['suggestion']));
                SuggestionManager::increaseScoreOf($search, $suggestion);
            }
            else {
                http_response_code(400);
            }
        }
        else {
            http_response_code(400);
        }
    }
    else {
      http_response_code(405);
    }