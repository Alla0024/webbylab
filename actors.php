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

            $response = [
                'status' => 200,
                'message' => "Actor deleted successfully"
            ];
        } else {
            $response = [
                'status' => 400,
                'message' => "Invalid request for delete_actor action."
            ];
        }
        break;

    case "get_actor_data":
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['actor_id'])) {
            $actorId = $_GET['actor_id'];

            $actor = Actor::getActorById($actorId);

            if ($actor) {
                $response = [
                    'data' => $actor,
                    'status' => 200,
                    'message' => ""
                ];
            } else {
                $response = [
                    'status' => 404,
                    'message' => "Actor not found"
                ];
            }
        } else {
            $response = [
                'status' => 400,
                'message' => "Invalid request for get_actor_data action."
            ];
        }
        break;

    case "search_actors":
        if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['searchValue'])) {
            $searchValue = $_POST['searchValue'];

            $results = Actor::searchActors($searchValue);
            $totalActors = count($results);
            $response = [
                'results' => $results,
                'status' => 200,
                'message' => ""
            ];
        } else {
            $response = [
                'status' => 400,
                'message' => "Invalid request for search_actors action."
            ];
        }
        break;

    case "update_actor":
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $actorId = $_POST['actor_id'];
            $firstName = trim($_POST['first_name']) ?: '';
            $lastName = trim($_POST['last_name']) ?: '';

            if (empty($firstName) || empty($lastName)) {
                $response = [
                    'status' => 400,
                    'message' => "Error: Please fill in all required fields."
                ];
            } else {
                if (empty($actorId)) {
                    Actor::addActor($firstName, $lastName);
                    $response = [
                        'status' => 200,
                        'message' => "Actor added successfully"
                    ];
                } else {
                    Actor::updateActor($actorId, $firstName, $lastName);
                    $response = [
                        'status' => 200,
                        'message' => "Actor updated successfully"
                    ];
                }
            }
        } else {
            $response = [
                'status' => 400,
                'message' => "Invalid request for update_actor action."
            ];
        }
        break;

    default:
        $response = [
            'status' => 400,
            'message' => "Invalid action."
        ];
        break;
}

header('Content-Type: application/json');
echo json_encode($response);
