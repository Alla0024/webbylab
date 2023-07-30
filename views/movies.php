<?php
$moviesPerPage = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
if (isset($_GET['search'])) {
    $searchValue = $_GET['search'];
    $movieData = Movie::searchMovies($searchValue, $page, $moviesPerPage);
} else {
    $movieData = Movie::getAllMovies($page, $moviesPerPage);
}
$movies = $movieData ['data'];
$totalMovies = $movieData ['total_count'];
$totalPages = ceil($totalMovies / $moviesPerPage);
$offset = ($page - 1) * $moviesPerPage;
?>
<div class="container-xl">
    <div class="table-responsive">
        <div class="table-wrapper">
            <div class="table-title">
                <div class="row">
                    <div class="col-sm-5">
                        <a href="index.php?action=movies"><h2>List of movies</h2></a>
                    </div>
                    <div class="col-sm-7">
                        <a href="javascript:;" class="btn btn-secondary" data-toggle="modal"
                           data-target="#movieModal"><i class="material-icons">&#xE147;</i>
                            <span>Add New Movie</span></a>
                        <a href="javascript:;" class="btn btn-secondary" id="exportBtn">
                            <i class="material-icons">cloud_download</i> <span>Export</span>
                        </a>
                        <a href="javascript:;" class="btn btn-secondary" id="importBtn">
                            <i class="material-icons">cloud_upload</i> <span>Import</span>
                        </a>

                        <div class="input-group">
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Enter your search term"
                                   value="<?php echo isset($searchValue) ? htmlspecialchars($searchValue) : ''; ?>">
                            <span class="input-group-icon"><i class="fas fa-search"></i></span>
                        </div>

                    </div>
                </div>
            </div>
            <table id="movieTable" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Movie title
                    </th>
                    <th>Release year</th>
                    <th>Format</th>
                    <th>Actor's name</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($movies as $movie) { ?>
                    <tr>
                        <td id="movieId"><?= $movie['id'] ?></td>
                        <td>
                            <?= htmlspecialchars($movie['title']) ?>
                        </td>
                        <td><?= $movie['release_year'] ?></td>
                        <td><?= $movie['format'] ?></td>
                        <td id="actorName"><?= $movie['actor_names'] ?></td>
                        <td id="actionButtons">
                            <a href="javascript:;" class="edit" title="Edit" data-toggle="modal" data-target="#movieModal" data-movie-id="<?= $movie['id'] ?>"><i class="material-icons">&#xE254;</i></a>
                            <a href="javascript:;" class="delete" title="Delete" data-toggle="modal" data-target="#deleteMovieModal" data-movie-id="<?= $movie['id'] ?>"><i class="material-icons">&#xE5C9;</i></a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <div class="clearfix">
                <div class="hint-text">
                    Showing
                    <b><?php echo htmlentities(min($totalMovies, $offset + $moviesPerPage), ENT_QUOTES, 'UTF-8'); ?></b>
                    out of <b><?php echo htmlentities($totalMovies, ENT_QUOTES, 'UTF-8'); ?></b> entries
                </div>
                <ul class="pagination">
                    <?php
                    echo generatePaginationLinks($page, $totalPages);
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<div id="messageOutput" class="message-output"></div>

<div class="modal" id="movieModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Movie Details</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="messageOutput"></div>
                <form id="movieForm">
                    <input type="hidden" id="movie_id" name="movie_id">
                    <label for="movie_title">Movie Title:</label>
                    <input type="text" id="movie_title" name="movie_title" required>
                    <label for="release_year">Release Year:</label>
                    <input type="text" id="release_year" name="release_year" required>
                    <div class="form-group">
                        <label for="format">Format</label>
                        <select class="form-control" id="format" name="format">
                            <option value="VHS">VHS</option>
                            <option value="DVD">DVD</option>
                            <option value="Blu-ray">Blu-ray</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="actor_name">Actor's Name:</label>
                        <select class="form-control" id="actor_name" name="actor_name[]" multiple required>
                            <?php
                            $actors = Actor::getListAllActors();
                            foreach ($actors as $actor) {
                                echo '<option value="' . $actor["id"] . '">'
                                    . $actor["first_name"] . ' ' . $actor["last_name"] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveMovieBtn">Save</button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="deleteMovieModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirm Deletion</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this movie?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="importModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="messageImportOutput"></div>
                <p>Upload a file with data:</p>
                <input type="file" id="fileInput">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="importFileBtn">Import</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>




