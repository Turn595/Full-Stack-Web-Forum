<?php

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Doctrine\ORM\EntityManager;

require_once "ArticleController.php";

class TagsController
{

    //Handles Getting tags from the database and returning an array of objects or null
    public static function getTags(EntityManager $em, array $reqData){

        //Use a query builder so we can sort our columns and specify directions.
        $qb = $em->createQueryBuilder();

        //Define a select query using the querybuilder's higher level functions.
        $qb->select('tag') //Select the desired Entity from the "->from" statement.
        ->from('Tag', 'tag')
        ->where('tag.articleId =' . $reqData['articleId']);

        if(isset($reqData['articleId']))
        {
            $qb->where('tag.articleId = :ident')
                ->setParameter('ident',$reqData['articleId']);
        }

        //This calls the query and converts the result into a usable array.
        $tagsArray = $qb->getQuery()->getArrayResult();

        //Display a error code is if not found.
        if (empty($tagsArray)){http_response_code(404);};

        return $tagsArray;
    }

    //Handles POST requests and SAVES/INSERT a new tags to the database.
    public static function postTags(EntityManager $em, array $reqData){

        $result = null; //Return variable - will encode JSON and send to the browser.
        $violations = []; //Array to store errors found during the validation process.

        //For loop to handle array of tags
        for($i=0;$i<sizeof($reqData['tag']);$i++) {

            //Check if tag is valid
            if (preg_match("/^\w*$/", $reqData['tag'][$i]) == 1) {

                if (isset($reqData['articleId']) && isset($reqData['token'])) {
                    if (ArticleController::verifyUserArticle($em, $reqData['articleId'], $reqData['token'])) {
                            try {
                                $tags = new Tag();
                                $tags->setArticleId($reqData['articleId']);
                                $tags->setTag($reqData['tag'][$i]);
                                //since $tags was passed into populateTags as a reference, new tags is populated with reqdata.
                                $em->persist($tags); //this saves the tags object into the in-memory database.
                                $em->flush(); //generate id for tags, save the in-memory objects to the sqlite database

                                http_response_code(201);//Created
                                $result .= "Success,"; //send back the successfully saved tags object

                            } catch (\Doctrine\ORM\ORMException $e) {
                                http_response_code(500);//databases Error.
                                $result = ['errorMessage' => $e->getMessage()];//assign DB error to result.
                            }

                    } else {
                        $result .= "Authentication Failed,";
                        http_response_code(401);
                    }
                } else {
                    $result .= "Authentication Failed,";
                    http_response_code(401);
                }
            } else {
                http_response_code(401);
                $result .= "Invalid Tag,";
            }
        }

        //This result is sent back to the TagsAPI where it is called and sent to the client.
        return $result;
    }

    //Handles the DELETE request to remove a tags from a database.
    public static function deleteTags(EntityManager $em, array $reqData, ?Tag $tagsToDelete){


        if (isset($reqData['token'])) {
            if (ArticleController::verifyUserArticle($em, $tagsToDelete->getArticleId(), $reqData['token'])) {
                if (is_null($tagsToDelete)) {
                    http_response_code(404); //If tags is not found.
                    $result = $reqData; //return the request data, as nothing was found.
                } else {
                    try {
                        //BEST PRACTICE: Compane the values from the reqData arary to the values in the tags from the database
                        //Compare the lastName and userName to input tags and delete if they are the same.
                        if ($reqData['id'] == $tagsToDelete->getId() && $reqData['tag'] == $tagsToDelete->getTag()) {

                            $em->remove($tagsToDelete); //Remove the tags in memory
                            $em->flush(); //save changes to DB.
                            http_response_code(204); //No content
                            $result = null;
                        } else {
                            http_response_code(422);
                            $result = ['errorMessage' => 'Tag information does not correspond with tags on file.'];
                        }
                    } catch (\Doctrine\ORM\ORMException $e) {
                        //the delete was accpeted but not allowed by the database = probably because of a foreign key constraint.
                        http_response_code(500);//Reset content - re-add the deleted user to you user interface.
                        $result = $tagsToDelete;//send back the tags from the database so the user can re-add the tags.
                    }
                }
            } else {
                $result = "Authentication Failed";
                http_response_code(401);
            }
        }else {
            $result = "Authentication Failed";
            http_response_code(401);
        }


        return $result;
    }


    /*************************************
     * HELPERS SECTION
     ************************************/

    //Helper to determine whether inputs are valid. Returns bool.
    public static function  populateTags(array $reqData, Tag &$tags, array &$violations = []): bool{
        $serializer = new Serializer([new ObjectNormalizer()],[]);

        //Copy the data from reqdata into the tags object, but skip the id and username fields.
        try {
            //get the data and assign it to a class.
            $serializer->denormalize($reqData, Tag::class, null,
                //assigned to populate a $tags object, ignore select attributes for the $tags.
                [ObjectNormalizer::OBJECT_TO_POPULATE => $tags, ObjectNormalizer::IGNORED_ATTRIBUTES => ['id','tag']]);
        }
        catch (\Symfony\Component\Serializer\Exception\ExceptionInterface $e)
        {
            $violations['errorMessage'] = $e->getMessage();
        }

        return empty($violations);//Return True if there are no validation errors.
    }

}