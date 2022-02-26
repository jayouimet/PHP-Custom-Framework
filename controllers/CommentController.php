<?php
    require_once dirname(__FILE__) . '/../src/Controller.php';
    require_once dirname(__FILE__) . '/../src/Request.php';
    require_once dirname(__FILE__) . '/../src/Response.php';
    require_once dirname(__FILE__) . '/../models/Comment.php';
    
    require_once dirname(__FILE__) . '/../src/providers/AuthProvider.php';

    class CommentController extends Controller {
        /**
         * Function called when trying to use the method Get on the home page
         *
         * @param Request $request
         * @param Response $response
         * @return void
         */
        public function getAddComment(Request $request, Response $response) {
            if (!AuthProvider::isAuthed())
                return $response->redirect('/');
            return $this->render('comments/addcomment', []);
        }

        public function getShowComment(Request $request, Response $response) {
            $comments = Comment::get();
            $params = [
                'comments' => $comments
            ];
            return $this->render('comments/showcomments', $params);
        }

        public function postAddComment(Request $request, Response $response) {
            if (!AuthProvider::isAuthed())
                return $response->redirect('/');
            $body = $request->getBody();
            /* TO DO ROSALIE : s'assurer que le body est une string > 0, si < 0 
            mettre un message d'erreur, avec un if, else, mettre "Veuillez entrer 
            un commentaire avant de soumettre" */
            $comment = new Comment();
            $comment->user_id = 1;
            $comment->post_id = 1;
            $comment->body = $body["comment"];

            $params = ['isInserted' => false];
            if ($comment->upsert()) {
                $params ['isInserted'] = true;
            }

            return $this->render('comments/addcomment', $params);
        }

        public function postComment(Request $request, Response $response) {
            if (!AuthProvider::isAuthed())
                return $response->redirect('/');

            $body = $request->getBody();
            
            $user = AuthProvider::getSessionObject();
            $post_id = $id = $request->getRouteParam('post_id');
            
            $comment = new Comment();

            $comment->user_id = $user->getId();
            $comment->post_id = $post_id;
            $comment->body = $body["comment"];

            $params = [];
            if (!$comment->upsert()) {
                $params['errorMessageId'] = 'unexpectedErrorAddComment';
            }

            return $response->redirect(('/posts/' . $post_id), $params);
        }
    }
?>