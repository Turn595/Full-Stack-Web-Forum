<?php
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Doctrine\ORM\EntityManager;

require_once "ArticleController.php";

class CommentsController
{
    //Handles Getting commentss from the database and returning an array of objects or null
    public static function getComments(EntityManager $em, array $reqData){

        //Use a query builder so we can sort our columns and specify directions.
        $qb = $em->createQueryBuilder();

        //Define a select query using the querybuilder's higher level functions.
        $qb->select('com') //Select the desired Entity from the "->from" statement.
        ->from('Comment', 'com')
        ->where('com.articleid =' . $reqData['articleid'])
        ->orderBy('com.date','DESC');

        //This calls the query and converts the result into a usable array.
        $commentsArray = $qb->getQuery()->getArrayResult();

        //Display a error code is if not found.
        if (empty($commentsArray)){http_response_code(404);}

        return $commentsArray;
    }

    //Handles POST requests and SAVES/INSERT a new comments to the database.
    public static function postComments(EntityManager $em, array $reqData, Comment $comments){

        $result = null; //Return variable - will encode JSON and send to the browser.
        $violations = []; //Array to store errors found during the validation process.
        //If last name in not null
        $comments->setDate(time() * 1000);

        if (isset($reqData['username']) && isset($reqData['token'])) {
            if (ArticleController::verifyUser($em, $reqData['username'], $reqData['token'])) {


                //The Populate helper function check for validation errors.
                if (self::populateComments($reqData, $comments, $violations)) {

                    try {
                        $em->persist($comments); //this saves the comments object into the in-memory database.
                        $em->flush(); //generate id for comments, save the in-memory objects to the sqlite database

                        http_response_code(201);//Created
                        $result = $comments; //send back the successfully saved comments object

                    } catch (\Doctrine\ORM\ORMException $e) {
                        http_response_code(500);//databases Error.
                        $result = ['errorMessage' => $e->getMessage()];//assign DB error to result.
                    }
                } else {
                    http_response_code(422); //Error unprocessable.
                    $result = $violations; // Send back the error.
                }
            }else {
                $result .= "Authentication Failed,";
                http_response_code(401);
            }
        }else {
            $result .= "Authentication Failed,";
            http_response_code(401);
        }

        //This result is sent back to the CommentsAPI where it is called and sent to the client.
        return $result;
    }

    //Handles the Put request, to UPDATE EXISTING commentss in the database.
    public static function putComments(EntityManager $em, array $newValues, ?Comment $commentsFromDB){

        $result = null; //the return value
        $violations = []; //Array to store errors found during the validation process.

        if($commentsFromDB->getUsername() === $newValues['username']) {
            if (ArticleController::verifyUser($em, $commentsFromDB->getUsername(), $newValues['token'])) {

                if (is_null($commentsFromDB)) {
                    http_response_code(404); //If comments is not found.
                    $result = $newValues; //Send back new values array.
                } elseif (self::populateComments($newValues, $commentsFromDB, $violations)) {
                    try {
                        //If modifying (and not creating) db entities, persist is not needed.
                        $em->flush(); //Save the entity to DB.
                        //http response code would be 200 - OK but that is the default so ne need to change.

                        $result = $commentsFromDB; //send back the successfully saved comments object

                    } catch (\Doctrine\ORM\ORMException $e) {
                        http_response_code(500);//DataBase Error
                        $result = ['errorMessage' => $e->getMessage()];
                    }

                } else {
                    http_response_code(422); //Unprocessable data.
                    $result = $violations; //Send back the errors.
                }
            } else {
                $result = "Authentication Failed";
                http_response_code(401);
            }
        } else {
            $result = "Authentication Failed";
            http_response_code(401);
        }
        return $result;
    }

    //Handles the DELETE request to remove a article from a database.
    public static function deleteComments(EntityManager $em, array $reqData, ?Comment $commentToDelete){

        //Verify the user
        if(ArticleController::verifyUser($em,$commentToDelete->getUsername(),$reqData['token'])) {

            if (is_null($commentToDelete)) {
                http_response_code(404); //If article is not found.
                $result = $reqData; //return the request data, as nothing was found.
            } else {
                try {
                    //BEST PRACTICE: Compane the values from the reqData arary to the values in the article from the database
                    //Compare the lastName and userName to input article and delete if they are the same.
                    if ($reqData['id'] == $commentToDelete->getId() && $reqData['username'] == $commentToDelete->getUsername()) {

                        $em->remove($commentToDelete); //Remove the article in memory
                        $em->flush(); //save changes to DB.
                        http_response_code(204); //No content
                        $result = null;
                    } else {
                        http_response_code(422);
                        $result = ['errorMessage' => 'Comment information does not correspond with article on file.'];
                    }
                } catch (\Doctrine\ORM\ORMException $e) {
                    //the delete was accepted but not allowed by the database = probably because of a foreign key constraint.
                    http_response_code(500);//Reset content - re-add the deleted user to you user interface.
                    $result = $commentToDelete;//send back the article from the database so the user can re-add the article.
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
    public static function  populateComments(array $reqData, Comment &$comments, array &$violations = []): bool{
        $serializer = new Serializer([new ObjectNormalizer()],[]);

        //Copy the data from reqdata into the comments object, but skip the id and commentname fields.
        try {
            //get the data and assign it to a class.
            $serializer->denormalize($reqData, Comment::class, null,
                //assigned to populate a $comments object, ignore select attributes for the $comments.
                [ObjectNormalizer::OBJECT_TO_POPULATE => $comments, ObjectNormalizer::IGNORED_ATTRIBUTES => ['id','hash']]);
        }
        catch (\Symfony\Component\Serializer\Exception\ExceptionInterface $e)
        {
            $violations['errorMessage'] = $e->getMessage();
        }

        return empty($violations);//Return True if there are no validation errors.
    }



}