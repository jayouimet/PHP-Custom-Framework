# PHP-Custom-Framework
Repository for a school project. The goal was to make a website in raw PHP but that was boring so we went further and made a framework for it.

## Initial setup
Follow the steps bellow to setup the server

### WAMP/MAMP
If you are using MAMP, you can start the MAMP server and skip this step
If you are using WAMP, after starting the server, left click on the WAMP Icon -> Apache -> Apache modules -> Activate rewrite_module

### Config file
To run this server, you need a config file containing some vital information.
On the root of the project, create a config.ini file containing the following : 
```
[database]
db_servername   = database_server_name
db_username     = database_username
db_password     = database_password
db_databasename = database_name

[server_config]
path_from_root  = /some/path
```
The path_from_root value represents where in your host service directory the root of the project is located at.
For exemple, if it is located under /www/TestFolder in WAMP, the value would be /TestFolder
If located under /htdocs/TestFolder in MAMP, the value would be /TestFolder

## Using the Router
Contrary to legacy PHP, we will not be able to access files directly using the directory arborescence. 
Instead, we map the URL to a certain resource file. Let's have a look at that.

### /public/index.php
In this file we map the url to their according controller functions, let's consider this exemple: 
```
<?php
    require_once '../src/Application.php';
    require_once '../controllers/HomeController.php';
    require_once '../controllers/ContactController.php';
    require_once '../controllers/UserController.php';
    
    $ini = parse_ini_file('../config.ini');

    $appConfig = [
        'baseUrl' => $ini['path_from_root'],
        'dbConfig' => [
            'servername' => $ini['db_servername'],
            'username' => $ini['db_username'],
            'password' => $ini['db_password'],
            'databasename' => $ini['db_databasename']
        ]
    ];

    $app = new Application(dirname(__DIR__), $appConfig);

    $app->router->set404("errors/404");

    $app->router->get('/', [HomeController::class, 'getHome']);

    // User Registration routes
    $app->router->get('/register', [UserController::class, 'getRegister']);

    $app->router->post('/register', [UserController::class, 'postRegister']);
    
    // Routes for adding a post
    $app->router->get('/addpost', [PostController::class, 'getAddPost']);
    $app->router->post('/addpost', [PostController::class, 'postAddPost']);
    
    // Route to show all posts
    $app->router->get('/posts', [PostController::class, 'getPosts']);

    // Route to show one post using the post id
    $app->router->get('/posts/{id}', [PostController::class, 'getPost']);

    $app->run();
?>
```
Here we route the GET for / to the function getHome in the HomeController class, and the routes /contact for both GET and POST to the functions getContact and postContact in the ContactController class. Same goes for the user registration and the test route.

### Controllers (/controllers/UserController.php)
Let's take for exemple the user controller: 
```
<?php
    require_once dirname(__FILE__) . '/../src/Controller.php';
    require_once dirname(__FILE__) . '/../src/Request.php';
    require_once dirname(__FILE__) . '/../src/Response.php';

    require_once dirname(__FILE__) . '/../src/providers/AuthProvider.php';
    
    require_once dirname(__FILE__) . '/../models/User.php';

    class UserController extends Controller {
        /**
         * Function called when trying to use the method GET on the user page
         *
         * @param Request $request The request
         * @param Response $response The response
         * @return void
         */
        public function getRegister(Request $request, Response $response) {
            return $this->render('users/adduser', []);
        }

        /**
         * Function called when trying to use the method POST on the user page
         *
         * @param Request $request The request
         * @param Response $response The response
         * @return void
         */
        public function postRegister(Request $request, Response $response) {
            /* We try to save the user sent from the request.body */
            $user = new User();

            $user->loadData($request->getBody());
            
            if($user->validate() && $user->register()){
                $response->redirect('/');
            }
            $params = [
                'user' => $user
            ];
            return $this->render('users/adduser', $params);
        }
    }
?>
```
Here we create a controller class which inherits from Controller in /src/Controller.php. Then we create the functions getRegister and postRegister that have been mapped above.
If we need to get informations from the request, for exemple the body, we can add the Request object as a parameter to this function (/src/Request.php).
We can also use the Response object as a second parameter to use methods such as Response->redirect($path).
In the postRegister we render the view located under /views/users/adduser.php and we give it the user as a parameter. 
We can then use the $user variable in the view file.

### Views (/views/home.php)
The views are basically our pages, it is what is getting shown to the user.
Here is an exemple view: 
#### /controllers/HomeController.php
```
<?php
    require_once '../src/Controller.php';

    class HomeController extends Controller {
        public function getHome(Request $request) {
            $params = [
                'name' => "TestName"
            ];
            return $this->render('home', $params);
        }
    }
?>
```
#### /views/home.php
```
<h1>Home</h1>
<h3>
    <?php echo $name?>
</h3>
```
#### /views/layouts/main.php
(Prone to changes)
```
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="container">
        {{content}}
    </div>
</body>
</html>
```
Here once we call the getHome function in the controller, we render the main.php layout (prone to change) which then replaces the content of the {{content}} block to what is rendered by /views/home.php.
Take note that we can use the variable $name in home.php because we have mapped it in the $params array in HomeController.php.

### Models
You can't have an MVC without the M.
Models represent our database objects and thus need to have a similar configuration. Our models will take care of querying the database and delete and upsert operations.

#### /models/User.php
```
<?php
    require_once dirname(__FILE__) . "/../src/database/DatabaseModel.php";
    require_once dirname(__FILE__) . "/../src/database/DatabaseEnums.php";
    require_once dirname(__FILE__) . "/../src/database/DatabaseRelation.php";
    
    require_once dirname(__FILE__) . '/../src/providers/AuthProvider.php';

    require_once dirname(__FILE__) . '/Post.php';

    class User extends DatabaseModel {
        /* Database attributes for User */
        public string $email = '';
        public string $username = '';
        public ?string $firstname = '';
        public ?string $lastname = '';
        public string $password = '';
        public string $passwordConfirm = '';
        public $date_of_birth = '';
        public bool $is_active;
        public ?string $confirmation_code;
        public $created_at;
        public $updated_at;
        public ?string $profile_picture = '';
        public ?string $status_message = '';

        public array $posts;
        public array $comments;

        protected static function relations(): array {
            return [
                new DatabaseRelation("posts", Post::class, "user_id", DatabaseRelationship::ONE_TO_MANY),
                new DatabaseRelation("comments", Comment::class, "user_id", DatabaseRelationship::ONE_TO_MANY),
            ];
        }


        public function rules(): array {
            return [
                'email' => [Rules::REQUIRED],
                'username' => [Rules::REQUIRED],
                'firstname' => [],
                'lastname' => [],
                'date_of_birth' => [Rules::REQUIRED],
                'password' => [Rules::REQUIRED, [Rules::MIN, 'min' => 8]],
                'passwordConfirm' => [Rules::REQUIRED, [Rules::MATCH, 'match' => 'password']],
            ];
        }

        protected static function table(): string
        {
            return 'users';
        }

        protected static function attributes(): array
        {
            return [
                'username' => DatabaseTypes::DB_TEXT,
                'email' => DatabaseTypes::DB_TEXT,
                'firstname' => DatabaseTypes::DB_TEXT, 
                'lastname' => DatabaseTypes::DB_TEXT, 
                'password' => DatabaseTypes::DB_TEXT, 
                'date_of_birth' => DatabaseTypes::DB_TEXT,
                'is_active' => DatabaseTypes::DB_INT,
                'confirmation_code' => DatabaseTypes::DB_TEXT
            ];
        }

        public function register(){
            $this->password = password_hash($this->password, PASSWORD_DEFAULT);
            if (parent::insert()) {
                AuthProvider::login($this);
                return true;
            }
            return false;
        }

        public static function login($username, $password) {
            $result = User::getOne(['username' => $username]);
            if ($result && password_verify($password, $result->password)) {
                AuthProvider::login($result);
                return $result;
            }
            return false;
        }
    }
?>
```
Here we create a User object with rules similar to the database. 
We must overwrite the table() function with the table name to which this Model is linked.
We must also overwrite the attributes() function so it returns a dictionary of key/value pairs where the key is the column name and the value is the data type.
Once this is done it is now possible to use the insert() function to automatically push the data for that user in the database.
We must also overwrite the relation method so our database connector knows how to generate sql requests from a model.

### Migrations
To ensure database consistency, we have created a beta version of a migration system.
The migration manager will keep track of up and down migrations and when using the following commands will update the database state as such.
These scripts must be executed from the project root directory :

#### php scripts/migrate_up.php
This script will go through the migrations under /src/database/migrations and will execute all the up.sql migrations in ascending order of epoch if they haven't been executed yet.

#### php scripts/migrate_down.php
This script will go through the migrations under /src/database/migrations and will execute all the down.sql migrations in descending order of epoch if their corresponding up migrations have been executed.
