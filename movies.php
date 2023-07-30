<?php
require_once("config/db.php");
require_once("models/movie.php");
require_once("models/actor.php");

function parseMovieData($movieData)
{
    $movies = explode("\n\n", $movieData);
    $parsedMovies = [];

    foreach ($movies as $movie) {
        $movieObj = [];
        $lines = explode("\n", $movie);

        foreach ($lines as $line) {
            $parts = explode(": ", $line, 2);
            if (count($parts) === 2) {
                list($key, $value) = $parts;
                $movieObj[strtolower(str_replace(" ", "_", trim($key)))] = trim($value);
            }
        }

        $parsedMovies[] = $movieObj;
    }

    return $parsedMovies;
}

function checkValidYear($year)
{
    $min_year = 1850;
    $max_year = 2023;

    if (is_numeric($year) && $year >= $min_year && $year <= $max_year) {
        return true;
    } else {
        return false;
    }
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'default';

$response = [
    'status' => 400,
    'message' => "Invalid action."
];

header('Content-Type: application/json');

switch ($action) {
    case "delete_movie":
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["movie_id"])) {
            $movieId = $_POST["movie_id"];
            Movie::deleteMovie($movieId);

            $response = [
                'status' => 200,
                'message' => "Movie deleted successfully"
            ];
        }
        break;

    case "get_movie_data":
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['movie_id'])) {
            $response = $_GET['movie_id'];

            $movie = Movie::getMovieById($response);

            if ($movie) {
                $response = [
                    'data' => $movie,
                    'status' => 200,
                    'message' => ""
                ];
            } else {
                $response = [
                    'status' => 404,
                    'message' => "Movie not found"
                ];
            }
        }
        break;

    case "import_movies":
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["movie_data"])) {
            $movieData = $_POST["movie_data"];
            $moviesToInsert = parseMovieData($movieData);

            $importMessages = '';
            $importedMovies = [];
            $existingMovies = [];

            foreach ($moviesToInsert as $movieObj) {
                if (isset($movieObj["title"]) && isset($movieObj["stars"]) && isset($movieObj["release_year"]) && isset($movieObj["format"])) {
                    $actorNames = explode(", ", $movieObj["stars"]);

                    $movieId = Movie::insertMovie($movieObj["title"], $movieObj["release_year"], $movieObj["format"]);
                    if ($movieId) {
                        foreach ($actorNames as $actorName) {
                            $actorFullNameParts = explode(" ", $actorName);
                            $actorFirstName = $actorFullNameParts[0];
                            $actorLastName = count($actorFullNameParts) > 1 ? $actorFullNameParts[1] : "";

                            $actorId = Actor::getOrCreateActor($actorFirstName, $actorLastName);

                            Actor::insertActorsMovies($actorId, $movieId);
                        }
                        $importedMovies[] = $movieObj["title"];
                    } else {
                        $existingMovies[] = $movieObj["title"];
                    }
                }
            }
            if ($importedMovies) {
                $importedMovies = implode(', ', $importedMovies);
                $importMessages .= '<h6>Imported successfully: </h6>' . $importedMovies . '<br><br>';
            }
            if ($existingMovies) {
                $existingMovies = implode(', ', $existingMovies);
                $importMessages .= '<h6>Already exists: </h6>' . $existingMovies . '<br><br>';

            }
            $response = [
                'imported_movies' => $importedMovies,
                'existing_movies' => $existingMovies,
                'status' => 200,
                'message' => $importMessages
            ];
        }
        break;
    case "export_movies":
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $response = $_GET['movie_id'];

            $txtContent = Movie::exportMoviesToTxt();
            if ($txtContent) {
                header('Content-Type: text/plain');
                header('Content-Disposition: attachment; filename="movies_data.txt"');
                echo $txtContent;
                exit;
            } else {
                $response = [
                    'status' => 400,
                    'message' => "Export failed."
                ];
            }

        }
        break;
    case "search_actors":
        if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['searchValue'])) {
            $searchValue = $_POST['searchValue'];

            $results = Movie::searchMovies($searchValue);
            $totalMovies = count($results);
            $response = [
                'results' => $results,
                'status' => 200,
                'message' => ""];
        } else {
            $response = [
                'status' => 200,
                'message' => "Invalid request for search_actors action."
            ];
        }
        break;

    case "update_movie":
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            $movieId = $_POST['movie_id'] ?: '';
            $movieTitle = trim($_POST['movie_title']) ?: '';
            $releaseYear = trim($_POST['release_year']) ?: '';
            $format = $_POST['format'] ?: '';
            $actorId = $_POST['actor_ids'] ?: '';

            $response = [
                'status' => 400,
                'message' => "Please fill in all required fields."
            ];

            if (!empty($movieTitle) && !empty($releaseYear) && !empty($format) && !empty($actorId)) {
                $valid_year = checkValidYear($releaseYear);
                if ($valid_year) {
                    if (!Movie::checkMovieExist($movieTitle, $releaseYear, $format, $movieId)) {
                        $response['status'] = 200;
                        if (empty($movieId)) {
                            Movie::addMovie($movieTitle, $releaseYear, $format, $actorId);
                            $response['message'] = "Movie added successfully";
                        } else {
                            Movie::updateMovie($movieId, $movieTitle, $releaseYear, $format, $actorId);
                            $response['message'] = "Movie updated successfully";
                        }
                    } else {
                        $response['message'] = "Movie already exists";
                    }
                } else {
                    $response['message'] = "Invalid release year. Please enter a year between 1850 and 2023.";
                }
            }
        }
        break;
}
echo json_encode($response);
