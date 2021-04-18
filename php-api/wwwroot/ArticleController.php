<?php

//Controllers usually have at least 4 functions for CRUD operations.
//CREATE - Usually with the POST methed
// READ - Usually sing teh GET method
// UPDATE - usually using the PUT method
// DELETE - usually using a DELETE method.

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use \Symfony\Component\Validator\Constraints\DateTime;
use Doctrine\ORM\EntityManager;


class ArticleController
{

    //Handles Getting articles from the database and returning an array of objects or null
    //Handles Getting articles from the database and returning an array of objects or null
    public static function getArticles(EntityManager $em, array $reqData){

        //Use a query builder so we can sort our columns and specify directions.
        $qb = $em->createQueryBuilder();

        //Define a select query using the querybuilder's higher level functions.
        $qb->select('art') //Select the desired Entity from the "->from" statement.
        ->from('Article', 'art')
            ->orderBy('art.date', 'DESC');

        //This calls the query and converts the result into a usable array.

        if(isset($reqData['id']))
        {
            $qb->where('art.id = :ident')
                ->setParameter('ident',$reqData['id']);
        }

        $articleArray = $qb->getQuery()->getArrayResult();

        if(isset($reqData['beginID']) && isset($reqData['range']))
        {
            if(in_array($reqData['beginID'],array_column($articleArray,'id')))
            {
                $beginKey = array_search($reqData['beginID'],array_column($articleArray,'id'));
                $articleArray = array_slice($articleArray,$beginKey,$reqData['range']);
            }
            else
            {
                http_response_code(422);
            }

        }


        //Display a error code is if not found.
        if (empty($articleArray)){http_response_code(404);};
        if(is_null($articleArray)){http_response_code(500);}

        return $articleArray;
    }

    //Handles POST requests and SAVES/INSERT a new article to the database.
    public static function postArticles(EntityManager $em, array $reqData, Article $newArticle){

        $result = null; //Return variable - will encode JSON and send to the browser.
        $violations = []; //Array to store errors found during the validation process.


        //Convert all data to html entities
        $reqData['title'] = htmlentities($reqData['title']);
        $reqData['subtitle'] = htmlentities($reqData['subtitle']);
        $reqData['body'] = htmlentities($reqData['body']);


        if(isset($reqData['username']) && isset($reqData['token'])) {
            if (self::verifyUser($em, $reqData['username'], $reqData['token'])) {

        //The Populate helper function check for validation errors.
        if (self::populateArticle($reqData, $newArticle, $violations) && strlen($reqData['title'])>=5) {

        //Setting default image
        if(!filter_var($reqData['bannerURL'], FILTER_VALIDATE_URL))
            $newArticle->setBannerURL(htmlentities("https://s3.envato.com/files/1170811/Video%20Preview%20Image_news.jpg"));
        else
            $reqData['bannerURL'] = htmlentities($reqData['bannerURL']);

        //Set the valid time
        $newArticle->setDate(time() * 1000);


                    try {
                        //since $article was passed into populateArticle as a reference, new article is populated with reqdata.
                        $em->persist($newArticle); //this saves the article object into the in-memory database.
                        $em->flush(); //generate id for article, save the in-memory objects to the sqlite database

                        http_response_code(201);//Created
                        $result = $newArticle; //send back the successfully saved article object

                    } catch (\Doctrine\ORM\ORMException $e) {
                        http_response_code(500);//databases Error.
                        $result = ['errorMessage' => $e->getMessage()];//assign DB error to result.
                    }
                } else {
                    http_response_code(422); //Error unprocessable.
                    $result = $violations; // Send back the error.
                }
            } else {
                $result = "Authentication Failed";
                http_response_code(401);
            }
        }
        else{
            $result = "Authentication Failed";
            http_response_code(401);
        }

        //This result is sent back to the ArticleAPI where it is called and sent to the client.
        return $result;
    }

    //Handles the Put request, to UPDATE EXISTING articles in the database.
    public static function putArticles(EntityManager $em, array $newValues, ?Article $articleFromDB){

        $result = null; //the return value
        $violations = []; //Array to store errors found during the validation process.

            if (self::verifyUser($em, $articleFromDB->getUsername(), $newValues['token'])) {

                if (is_null($articleFromDB)) {
                    http_response_code(404); //If article is not found.
                    $result = $newValues; //Send back new values array.
                } elseif (self::populateArticle($newValues, $articleFromDB, $violations) && strlen($newValues['title'])>=5) {
                    try {
                        //If modifying (and not creating) db entities, persist is not needed.
                        $em->flush(); //Save the entity to DB.
                        //http response code would be 200 - OK but that is the default so ne need to change.

                        $result = $articleFromDB; //send back the successfully saved article object

                    } catch (\Doctrine\ORM\ORMException $e) {
                        http_response_code(500);//DataBase Error
                        $result = ['errorMessage' => $e->getMessage()];
                    }

                } else {
                    http_response_code(422); //Unprocessable data.
                    $result = $violations; //Send back the errors.
                }
            } else{ $result = "Authentication Failed"; http_response_code(401);}


        return $result;
    }

    //Handles the DELETE request to remove a article from a database.
    public static function deleteArticles(EntityManager $em, array $reqData, ?Article $articleToDelete){

        //Verify the user
        if(self::verifyUser($em,$articleToDelete->getUsername(),$reqData['token'])) {

            if (is_null($articleToDelete)) {
                http_response_code(404); //If article is not found.
                $result = $reqData; //return the request data, as nothing was found.
            } else {
                try {
                    //BEST PRACTICE: Compane the values from the reqData arary to the values in the article from the database
                    //Compare the lastName and userName to input article and delete if they are the same.
                    if ($reqData['id'] == $articleToDelete->getId() && $reqData['title'] == $articleToDelete->getTitle()) {

                        $em->remove($articleToDelete); //Remove the article in memory
                        $em->flush(); //save changes to DB.
                        http_response_code(204); //No content
                        $result = null;
                    } else {
                        http_response_code(422);
                        $result = ['errorMessage' => 'Article information does not correspond with article on file.'];
                    }
                } catch (\Doctrine\ORM\ORMException $e) {
                    //the delete was accpeted but not allowed by the database = probably because of a foreign key constraint.
                    http_response_code(500);//Reset content - re-add the deleted user to you user interface.
                    $result = $articleToDelete;//send back the article from the database so the user can re-add the article.
                }
            }
        } else {
            $result = "Authentication Failed";
            http_response_code(401);
        }
        return $result;
    }


    /*************************************
     * HELPERS SECTION
     ************************************/

    //Helper to determine whether inputs are valid. Returns bool.
    public static function  populateArticle(array $reqData, Article &$article, array &$violations = []): bool{
        $serializer = new Serializer([new ObjectNormalizer()],[]);

        //Copy the data from reqdata into the article object, but skip the id and username fields.
        try {
            //get the data and assign it to a class.
            $serializer->denormalize($reqData, Article::class, null,
                //assigned to populate a $article object, ignore select attributes for the $article.
                [ObjectNormalizer::OBJECT_TO_POPULATE => $article, ObjectNormalizer::IGNORED_ATTRIBUTES => ['id','date']]);
        }
        catch (\Symfony\Component\Serializer\Exception\ExceptionInterface $e)
        {
            $violations['errorMessage'] = $e->getMessage();
        }

        return empty($violations);//Return True if there are no validation errors.
    }

    //Verify if username and token is correct
    public static function verifyUser(EntityManager $em, String $username, String $token)
    {
        $user = new User();

        //Attempt to get user
        try {
            $user = $em->find(User::class, $username);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
        } catch (\Doctrine\ORM\TransactionRequiredException $e) {
        } catch (\Doctrine\ORM\ORMException $e) {
        }
        if(is_null($user)){ return false; }
        return password_verify($token,$user->getTokenHash());
    }

    //Verify if username and token is correct
    public static function verifyUserArticle(EntityManager $em, int $articleId, String $token)
    {
        $article = new Article();
        //Attempt to get Article to get username
        try {
            $article = $em->find(Article::class, $articleId);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
        } catch (\Doctrine\ORM\TransactionRequiredException $e) {
        } catch (\Doctrine\ORM\ORMException $e) {
        }

        //If article is null return false
        if(is_null($article)){ return false; }

        $user = new User();

        //Attempt to get user
        try {
            $user = $em->find(User::class, $article->getUsername());
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
        } catch (\Doctrine\ORM\TransactionRequiredException $e) {
        } catch (\Doctrine\ORM\ORMException $e) {
        }
        //If user is null return false
        if(is_null($user)){ return false; }
        return password_verify($token,$user->getTokenHash());
    }


}