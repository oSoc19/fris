<?php
    class Suggestion
    {
        private $word;
        private $score;


        public function __construct($word, $score)
        {
            $this->word = $word;
            $this->score = $score;
        }


        public function __set($property, $value) {
            if(property_exists($this, $property))
                $this->$property = $value;
        }

        public function __get($property) {
            return $this->$property;
        }

    }
