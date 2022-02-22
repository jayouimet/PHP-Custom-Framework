<?php
    require_once dirname(__FILE__) . "/../src/database/DatabaseModel.php";
    require_once dirname(__FILE__) . '/User.php';

    class Post extends DatabaseModel {
        /* Database attributes for the table posts */
        public string $title = '';
        public string $body = '';
        public ?int $user_id = null;

        public User $user;

        public function user() : User {
            return User::getOne(['id' => $this->user_id]);
        }

        /*public function fetch() {
            if (!isset($this->user) || $this->user->getId() !== 0)
                $this->user = User::getOne(['id' => $this->user_id]);
        }*/

        protected static function relations(): array {
            return [
                new DatabaseRelation("user", User::class, "user_id", DatabaseRelationship::MANY_TO_ONE),
            ];
        }


        public function rules(): array {
            return [
                'title' => [Rules::REQUIRED],
                'body' => [],
            ];
        }

        protected static function table(): string
        {
            return 'posts';
        }

        protected static function attributes(): array
        {
            return [
                'title' => DatabaseTypes::DB_TEXT,
                'body' => DatabaseTypes::DB_TEXT,
                'likes' => DatabaseTypes::DB_INT,
                'user_id' => DatabaseTypes::DB_INT
            ];
        }
    }
?>