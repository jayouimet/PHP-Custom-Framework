<?php
    require_once dirname(__FILE__) . '/../src/Controller.php';
    require_once dirname(__FILE__) . '/../src/Request.php';
    require_once dirname(__FILE__) . '/../src/Response.php';

    require_once dirname(__FILE__) . '/../models/User.php';
    require_once dirname(__FILE__) . '/../models/Post.php';
    require_once dirname(__FILE__) . '/../models/Comment.php';

    require_once dirname(__FILE__) . '/../src/providers/AuthProvider.php';

    class PostController extends Controller {
        /**
         * Function called when trying to use the method GET on the post page
         *
         * @param Request $request The request
         * @param Response $response The response
         * @return void
         */
        public function getAddPost(Request $request, Response $response) {
            if (!AuthProvider::isAuthed())
                return $response->redirect('/');
            /* Create a post model to then give it to the registration form */
            $postModel = new Post();
            $params = [
                'model' => $postModel
            ];
            return $this->render('posts/addPost', $params);
        }

        /**
         * Function called when trying to use the method POST on the post page
         *
         * @param Request $request The request
         * @param Response $response The response
         * @return void
         */
        public function postAddPost(Request $request, Response $response) {
            if (!AuthProvider::isAuthed())
                return $response->redirect('/');
            /* We try to save the post sent from the request.body */
            $user = AuthProvider::getSessionObject();
            $post = new Post();
            $post->loadData($request->getBody());

            if($post->validate()){
                $user->posts[] = $post;
                $user->upsert();
                $response->redirect('/posts/' . $post->getId());
            }

            $params = [
                'model' => $post
            ];
            return $this->render('posts/addPost', $params);
        }

        /**
         * Function called when trying to use the method GET on the post page to see all posts
         *
         * @param Request $request The request
         * @param Response $response The response
         * @return void
         */
        public function getPosts(Request $request, Response $response) {
            /* Create a post model to then give it to the registration form */
            $posts = Post::get([]);
            $params = [
                'posts' => $posts
            ];
            return $this->render('posts/showPosts', $params);
        }

        public function getPost(Request $request, Response $response) {
            /* Create a post model to then give it to the registration form */
            $id = $request->getRouteParam('id');
            $post = Post::getOne(['id' => $id]);
            $post->fetch();
            if (!$post) {
                return $this->render('errors/404', []);
            }
            $params = [
                'post' => $post
            ];
            return $this->render('posts/showPost', $params);
        }
    }
?>