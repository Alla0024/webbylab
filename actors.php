<?php
require_once("config/db.php");
require_once("models/movie.php");
require_once("models/actor.php");

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'default';

switch ($action) {
    case "delete_actor":
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["actor_id"])) {
            $actorId = $_POST["actor_id"];
            Actor::deleteActor($actorId);

            echo "Actor deleted successfully";
        } else {
            echo "Invalid request for delete_actor action.";
        }
        break;

    case "get_actor_data":
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['actor_id'])) {
            $actorId = $_GET['actor_id'];

            $actor = Actor::getActorById($actorId);

            if ($actor) {
                header('Content-Type: application/json');
                echo json_encode($actor);
            } else {
                header('HTTP/1.1 404 Not Found');
                echo "Actor not found";
            }
        } else {
            header('HTTP/1.1 400 Bad Request');
            echo "Invalid request for get_actor_data action.";
        }
        break;

    case "search_actors":
        if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['searchValue'])) {
            $searchValue = $_POST['searchValue'];

            $results = Actor::searchActorsByName($searchValue);
            $totalActors = count($results);
            $response = array(
                'actors' => $results,
                'totalActors' => $totalActors
            );
            echo json_encode($response);
        } else {
            echo "Invalid request for search_actors action.";
        }
        break;

    case "update_actor":
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $actorId = $_POST['actor_id'];
            $firstName = $_POST['first_name'];
            $lastName = $_POST['last_name'];

            if (empty($firstName) || empty($lastName)) {
                http_response_code(400);
                echo "Error: Please fill in all required fields.";
                exit;
            }

            if (empty($actorId)) {
                Actor::addActor($firstName, $lastName);
                echo "Actor added successfully";
            } else {
                Actor::updateActor($actorId, $firstName, $lastName);
                echo "Actor updated successfully";
            }
        } else {
            echo "Invalid request for update_actor action.";
        }
        break;

    default:
        echo "Invalid action.";
        break;
}
?>
