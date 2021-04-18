<?php

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use Doctrine\ORM\EntityManager;


class UsersController
{
    //Handles Getting userss from the database and returning an array of objects or null
    public static function getUsers(EntityManager $em, array $reqData){
        $result = "Authentication Failed";
        $user = new User();

        //Attempt to get User from db
        try {
        $user = $em->find(USER::class, $reqData['username']);
        } catch (\Doctrine\ORM\OptimisticLockException $e) {
            http_response_code(401);
            $result = $e->getMessage();
        } catch (\Doctrine\ORM\TransactionRequiredException $e) {
            http_response_code(401);
            $result = $e->getMessage();
        } catch (\Doctrine\ORM\ORMException $e) {
            http_response_code(401);
            $result = $e->getMessage();
        }

        if(!is_null($user)) {
            if (password_verify($reqData['password'], $user->getHash())) {
                try {
                    $result = bin2hex(random_bytes(30));
                    $user->setTokenHash(password_hash($result,PASSWORD_BCRYPT));
                    $em->flush();
                } catch (Exception $e) {
                    http_response_code(401);
                }
            } else {
                http_response_code(401);
            }
        }
        else{
            http_response_code(401);
        }


        return $result;
    }

    //Handles POST requests and SAVES/INSERT a new users to the database.
    public static function postUsers(EntityManager $em, array $reqData, User $users){
        //Default response code
        http_response_code(401);

        $result = "Username or Email already in use"; //Return variable - will encode JSON and send to the browser.
        $violations = []; //Array to store errors found during the validation process.
//username,email,password
        if(preg_match("/^\w*$/",$reqData['username'])==1) {
            //The Populate helper function check for validation errors.
            if (self::populateUsers($reqData, $users, $violations) && strlen($reqData['password']) <= 60) {
                //Password is a extra field that is needed
                try {
                    $users->setUsername($reqData['username']);
                    $users->setHash(password_hash($reqData['password'], PASSWORD_BCRYPT));
                    $users->setTokenHash(password_hash(bin2hex(random_bytes(512)), PASSWORD_BCRYPT));
                    //since $users was passed into populateUsers as a reference, new users is populated with reqdata.
                    $em->persist($users); //this saves the users object into the in-memory database.
                    $em->flush(); //generate id for users, save the in-memory objects to the sqlite database

                    http_response_code(201);//Created
                    $result = $users->getUsername(); //send back the successfully saved users object

                } catch (\Doctrine\ORM\ORMException $e) {
                    http_response_code(500);//databases Error.
                    $result = null;//assign DB error to result.
                } catch (Exception $e) {
                    $result = null;
                }
            } else {
                http_response_code(401);
                $result = $violations; // Send back the error.
                if (empty($violations)) {
                    $result = "Password exceeds 60 characters";
                }
            }
        }
        else{ http_response_code(401); $result = "Invalid Username";}


        //This result is sent back to the UsersAPI where it is called and sent to the client.
        return $result;
    }

    //Handles the Put request, to UPDATE EXISTING userss in the database.
    public static function putUsers(EntityManager $em, array $newValues, ?User $usersFromDB)
    {

        $result = null; //the return value
        $violations = []; //Array to store errors found during the validation process.

        if(password_verify($newValues['token'],$usersFromDB->getTokenHash())) {

            if (isset($newValues['password']) && strlen($newValues['password'])<=60) {
                $usersFromDB->setHash(password_hash($newValues['password'], PASSWORD_BCRYPT));
            }

            if (is_null($usersFromDB)) {
                http_response_code(404); //If users is not found.
                $result = $newValues; //Send back new values array.
            } elseif (self::populateUsers($newValues, $usersFromDB, $violations)) {
                try {
                    //If modifying (and not creating) db entities, persist is not needed.
                    $em->flush(); //Save the entity to DB.
                    //http response code would be 200 - OK but that is the default so ne need to change.

                    $result = true; //send back if changes were made

                } catch (\Doctrine\ORM\ORMException $e) {
                    http_response_code(500);//DataBase Error
                    $result = ['errorMessage' => $e->getMessage()];
                }

            } else {
                http_response_code(422); //Unprocessable data.
                $result = $violations; //Send back the errors.
            }
        }
        else{
            $result="Authentication Failed";
            http_response_code(401);
        }
        return $result;
    }



    //Handles the DELETE request to remove a User from a database.
    public static function deleteUsers(EntityManager $em, array $reqData, ?User $userToDelete){

        //Verify the user
        if(password_verify($reqData['token'],$userToDelete->getTokenHash())) {

            if (is_null($userToDelete)) {
                http_response_code(404); //If article is not found.
                $result = $reqData; //return the request data, as nothing was found.
            } else {
                try {
                    //BEST PRACTICE: Compane the values from the reqData arary to the values in the article from the database
                    //Compare the lastName and userName to input article and delete if they are the same.
                    if ($reqData['username'] == $userToDelete->getUsername()) {

                        $em->remove($userToDelete); //Remove the article in memory
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
                    $result = "Delete not allowed by database";
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
    public static function  populateUsers(array $reqData, User &$users, array &$violations = []): bool{
        $serializer = new Serializer([new ObjectNormalizer()],[]);

        //Copy the data from reqdata into the users object, but skip the hash and tokenHash fields.
        try {
            //get the data and assign it to a class.
            $serializer->denormalize($reqData, User::class, null,
                //assigned to populate a $users object, ignore select attributes for the $users.
                [ObjectNormalizer::OBJECT_TO_POPULATE => $users, ObjectNormalizer::IGNORED_ATTRIBUTES => ['username','hash','tokenHash']]);
        }
        catch (\Symfony\Component\Serializer\Exception\ExceptionInterface $e)
        {
            $violations['errorMessage'] = $e->getMessage();
        }

        return empty($violations);//Return True if there are no validation errors.
    }

}