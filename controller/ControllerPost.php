<?php

require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'model/Post.php';
require_once 'model/User.php';
require_once 'model/Tag.php';



class ControllerPost extends Controller {

	public function index() {
		$user= $this->get_user_or_false();
		$numberQuestions =Post::total_questions();
		$totalPages = $numberQuestions  / 5;
		if(is_float($totalPages)){
			$totalPages =(int) ($totalPages+1);
		}
		$currentPage;
		$tagName = "";
		$menu="";
		$search="";
		$posts = array();
		if(isset($_POST['search'])){
			$search=$_POST['search'];
			if(!(strlen(trim($search)) == 0)){
				$Search ="%".$search."%";
				$posts = Post::get_searchs($Search);
			}			
		}else{
			if(isset($_GET["param1"])){
				$menu = $_GET["param1"];		
			}
			else{
				$menu = "newest";
			}
			if(isset($_GET["param2"])){
				$currentPage = $_GET["param1"];		
			}
			else{
				$currentPage = 1;
			}
			if($menu == "newest"){
				$posts = Post::get_questions_newest();
			}else if($menu == "votes"){
				$posts = Post::get_questions_votes();
			}else if($menu == "unanswered"){
				$posts = Post::get_questions_unanswered();
			}
		}
		(new View("index"))->show(array("posts" => $posts,"user" => $user, "search" => $search, "menu" => $menu,
		"tagName" =>$tagName, "totalPages"=>$totalPages));		
    }

	public function ask(){
		$title="";
		$body="";
		$errors = [];
		$checked = false;
		$user = $this->get_user_or_false();
		$tags = Tag::get_tags();

		if($user){
			$choix= array();
			if (isset($_POST['body']) && isset($_POST['title'])) {
				$body = $_POST['body'];
				$title = $_POST['title'];
				$choix=$_POST['choix'];
				//var_dump($choix);
				$authorId = User::get_id_by_userName($user->userName);
				$post = new Post($authorId,$title,$body ,NULL,NULL);			
				
				$errors = $post->validate_question();
				$checked = true;
				if(count($errors) == 0){
					$post->addPost();	
				}
			}		
			if(($checked == true) && (count($errors) == 0)){
				$postid = $post->get_postid();
				//var_dump($choix);
				if(!empty($choix)){
					if(count($choix)<= max_tags){
					foreach($choix as $key => $val){
						//var_dump($val);
						$post->addTag($val);
						}
					}
				}
				
				
				$this->redirect("post","show",$postid);				
			}else{
				(new View("ask"))->show(array("title" => $title, "body" => $body,"user" => $user, "errors" => $errors, "tags"=>$tags));
			}
		}else{
			(new View("error"))->show(array());
		}
		
		
	}
	
	public function show(){
		$user = "";
		$id = "";
		$question = "";
		$answerAccepted ="";
		
		if(isset($_GET["param1"])){
			$id = $_GET["param1"];
			$user = $this->get_user_or_false();
			$question = Post::get_post($id);
			if($question->is_question()){
				$reponse="";
				$authorId ="";
				$begin = array($question);
				if($question->acceptedAnswerId !== NULL){
					$answerAccepted = Post::get_post($question->acceptedAnswerId);
					array_push($begin,$answerAccepted);
				}
				$errors =array();
				if(isset($_POST['answer'])){
					$reponse=$_POST['answer'];
					$post = new Post($user->get_id(),"",$reponse,NULL,$question->get_postid());
					$errors = $post->validate_answer();
					if(count($errors) == 0){
						$post->addPost(); 				
					}
				}		
				$reponses = $question->get_answers($question);
				$posts = array_merge($begin,$reponses);
				(new View("question"))->show(array("question" => $question,"user" => $user, "errors" => $errors , "posts" =>$posts, "id" =>$id));	
			}else{
				(new View("error"))->show(array());
			}
		}else{
			(new View("error"))->show(array());
		}
	}
	
	
	public function accept(){
		$questionId="";
		$answerId="";
		if(isset($_GET["param1"])){
			$questionId=$_GET["param1"];
			if(isset($_POST['decliner'])){
				$answerId=NULL;
				Post::addAccepterAnswer($questionId,$answerId);
			}
			if(isset($_GET["param2"])){
				$answerId=$_GET["param2"];
				if(isset($_POST['accepter'])){
					Post::addAccepterAnswer($questionId,$answerId);
				}	
			}else{
				(new View("error"))->show(array());
			}
			$this->redirect("post","show",$questionId);	
		}else{
			(new View("error"))->show(array());
		}
	}
	
	public function edit(){
		if(isset($_GET["param1"])){
			$user = $this->get_user_or_redirect();
			$postid = $_GET["param1"];
			$post = Post::get_post($postid);
			$is_question = $post->is_question();
			$title = "";
			$body = "";
			$errors= array();
			if(isset($_POST['modifier'])){		
				$body = $_POST['body'];
				$title =$_POST['title'];
				$post = new Post($postid,$title,$body ,NULL,NULL);
				if($is_question == false){	
					$errors = $post->validate_answer();
					if(count($errors) == 0){
						Post::editPost($title,$body,$postid);
						$post = Post::get_post($postid);
						$this->redirect("post","show", $post->parentId);
					}else{
						(new View("edit"))->show(array("title" => $title, "body" => $body, "postid" => $postid,"user" =>$user, "errors" =>$errors, "is_question" =>$is_question));					
					}				
				}else{
					$is_question = true;
					$errors = $post->validate_question();
					if(count($errors) == 0){
						Post::editPost($title,$body,$postid);
						$this->redirect("post","show",$postid);
					}
					else{
						(new View("edit"))->show(array("title" => $title, "body" => $body, "postid" => $postid,"user" =>$user, "errors" =>$errors, "is_question" =>$is_question));
					}
				}
			}else{	
				$post = Post::get_post($_GET["param1"]);
				$is_question = $post->is_question();
				$title = $post->title;
				$body = $post->body;	
				(new View("edit"))->show(array("title" => $title, "body" => $body, "postid" => $postid,"user" =>$user,"errors" =>$errors, "is_question" =>$is_question));
			}
		}else{
			(new View("error"))->show(array());
		}
	}
	
	
	public function confirm_delete(){
		$controller = 1;
		$user = $this->get_user_or_false();
		if(isset($_GET["param1"]) && $user){		
			$id = $_GET["param1"];
			$post = Post::get_post($id);
			if(isset($_POST['annuler'])){		
				if($post->title =="" || $post->title == NULL){//reponse OK
					$this->redirect("post","show", $post->parentId);
				}else{//question
					$this->redirect("post","index");
				}
			}
			if(isset($_POST['supprimer'])){
				Post::deletePost($id);
				if($post->title =="" || $post->title == NULL){//reponse OK
					$this->redirect("post","show", $post->parentId);
				}else{//question
					$this->redirect("post","index");
				}
			}		
			(new View("delete"))->show(array("id" => $id,"user" =>$user,"controller" => $controller));
		}else{
			(new View("error"))->show(array());
		}
	}
	
	public function posts(){
		$user=$this->get_user_or_false();
		$search='';
		$posts = array();
		$totalPages = 3;
		$menu;
		if(isset($_GET['param1']) && $_GET['param1']=='tag'){
			$tag = $_GET['param1'];
			$menu = $tag;
			if(isset($_GET['param2']) && isset($_GET['param3'])){
				$tagId = $_GET['param3'];
				$page = $_GET['param2'];
				$tag = Tag::get_tag($tagId);
				$posts = Post::get_questions_bytag($tagId,$page);
				(new View("index"))->show(array("posts" => $posts,"user" => $user, "search" => $search, "menu" => $menu,"tagName" =>$tag->tagName, "totalPages"=>$totalPages));
			}
		}	
	}
	
	public function takeoff_tag(){
		$tagId;
		$postId;
		if(isset($_GET['param1']) && isset($_GET['param2'])){
			$tagId = $_GET['param1'];
			$postId = $_GET['param2'];
			Tag::takeoff($tagId,$postId);
			$this->redirect("post","show", $postId);
		}else{
		}
	}
	
	public function addTag(){
		$tagId;
		$postId;
		if(isset($_GET['param1'])){
			$postId = $_GET['param1'];
			$post = Post::get_post($postId);
			if(isset($_POST['tag'])){
				$tagId= $_POST['tag'];
				$post->addTag($tagId);
				$this->redirect("post","show", $postId);
			}
		}else{
		}
	}
}
