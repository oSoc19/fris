<?php
    // All classes & managers
    spl_autoload_register(function($class) {
        if(strpos($class, "Manager") !== false)
            include __DIR__ . "../manager/" . $class . ".php";
        else
            include __DIR__ . "../class/" . $class . ".php";
    });

    class SuggestionManager
    {
        public static function isExistingSuggestion($suggestion) {
            $suggestion = str_replace('%20', ' ', $suggestion);
            return DBManager::isExistingRecord('suggestion', 'word', $suggestion);
        }

        public static function isExistingSearch($search) {
            $search = str_replace('%20', ' ', $search);
            return DBManager::isExistingRecord('search', 'word', $search);
        }

        public static function insertSuggestion($suggestion) {
            $suggestion = str_replace('%20', ' ', $suggestion);
            DBManager::insert('INSERT INTO suggestion(word) VALUES(:word)', ['word' => $suggestion]);
        }

        public static function insertSearch($search) {
            $search = str_replace('%20', ' ', $search);
            DBManager::insert('INSERT INTO search(word) VALUES(:word)', ['word' => $search]);
        }

        public static function insertSearchSuggestion($search, $suggestion, $score) {
            $search = str_replace('%20', ' ', $search);
            $suggestion = str_replace('%20', ' ', $suggestion);
            DBManager::insert('INSERT INTO search_suggestion(search_id, suggestion_id, score) VALUES((SELECT search_id FROM search WHERE word = :search), (SELECT suggestion_id FROM suggestion WHERE word = :suggestion), :score)', ['search' => $search, 'suggestion' => $suggestion, 'score' => $score]);
        }

        public static function increaseScoreOf($search, $suggestion) {
            $search = str_replace('%20', ' ', $search);
            $suggestion = str_replace('%20', ' ', $suggestion);
            DBManager::update('UPDATE search_suggestion SET score = score + 1 WHERE search_id = (SELECT search_id FROM search WHERE word = :search) AND suggestion_id = (SELECT suggestion_id FROM suggestion WHERE word = :suggestion)', ['search' => $search, 'suggestion' => $suggestion]);
        }

        public static function getSearchInfos($search) {
            $search = str_replace('%20', ' ', $search);
            return DBManager::selectAll('SELECT su.word, ss.score FROM search_suggestion as ss, search as se, suggestion as su WHERE se.word = :search AND se.search_id = ss.search_id AND ss.suggestion_id = su.suggestion_id ORDER BY score DESC', ['search' => $search]);
        }

        public static function getSuggestions($lang, $search) {
            if ($lang == 'en') {
                return self::getWordList($search);
            } else if ($lang == 'nl') {
                return self::getWordList($search);
            } else {
                return [];
            }
        }

        public static function getWordList($search) {
            $search = str_replace(' ', '%20', $search);
            $suggestions = [];
            if(self::isExistingSearch($search)) { // if search is already in DB get all suggestions
                $wordsDB = self::getSearchInfos($search);
                foreach ($wordsDB as $word) {
                    array_push($suggestions, new Suggestion($word['word'], $word['score']));
                }
            }
            else { // If not, call the API
                self::insertSearch($search);
                $response = file_get_contents('https://api.datamuse.com/words?ml=' . $search);
                $wordsAPI = json_decode($response, true);
                if(count($wordsAPI) > 0) {
                    // sort by score and keep top 10
                    function my_sort_function($a, $b) {
                        if(array_key_exists('score', $a) && array_key_exists('score', $b)) {
                            return $a['score'] < $b['score'];
                        }
                        else if(array_key_exists('score', $a)) {
                            return false;
                        }
                        else if(array_key_exists('score', $b)) {
                            return true;
                        }
                        return false;
                    }
                    usort($wordsAPI, 'my_sort_function');
                    $wordsAPI = array_slice($wordsAPI, 0, 10);

                    foreach ($wordsAPI as $word) {
                        if(strlen($word['word']) > 2) {
                            $suggestion = new Suggestion($word['word'], 0);
                            array_push($suggestions, $suggestion);
                            if(!self::isExistingSuggestion($suggestion->word)) {
                                self::insertSuggestion($suggestion->word);
                            }
                            self::insertSearchSuggestion($search, $suggestion->word, 0);
                        }
                    }
                }
            }

            return $suggestions;
        }
    }
