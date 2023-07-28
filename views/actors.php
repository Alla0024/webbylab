<?php
$actors = Actor::getAllActors();

$actorsPerPage = 10;
$totalActors = count($actors);
$totalPages = ceil($totalActors / $actorsPerPage);

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $actorsPerPage;
$actors = array_slice($actors, $offset, $actorsPerPage);
?>
<div class="container-xl">
    <div class="table-responsive">
        <div class="table-wrapper">
            <div class="table-title">
                <div class="row">
                    <div class="col-sm-5">
                        <h2>List of actors</h2>
                    </div>
                    <div class="col-sm-7">
                        <a href="#" class="btn btn-secondary" data-toggle="modal" data-target="#actorModal"><i class="material-icons">&#xE147;</i> <span>Add New Actor</span></a>

                        <div class="input-group">
                            <input type="text" name="search" id="searchInput" class="form-control" placeholder="Enter your search term">
                            <span class="input-group-icon"><i class="fas fa-search"></i></span>
                        </div>

                    </div>
                </div>
            </div>
            <table id="actorTable" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($actors as $actor) { ?>
                    <tr>
                        <td id="actorId"><?= $actor['id'] ?></td>
                        <td><?= $actor['first_name'] ?></td>
                        <td><?= $actor['last_name'] ?></td>
                        <td id="actionButtons">
                            <a href="#" class="edit_actor" title="Edit" data-toggle="modal" data-target="#actorModal" data-actor-id="<?= $actor['id'] ?>"><i class="material-icons">&#xE254;</i></a>
                            <a href="#" class="delete_actor" title="Delete" data-toggle="modal" data-target="#deleteActorModal" data-actor-id="<?= $actor['id'] ?>"><i class="material-icons">&#xE5C9;</i></a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <div class="clearfix">
                <div class="hint-text">
                    Showing
                    <b><?php echo htmlentities(min($totalActors, $offset + $actorsPerPage), ENT_QUOTES, 'UTF-8'); ?></b>
                    out of <b><?php echo htmlentities($totalActors, ENT_QUOTES, 'UTF-8'); ?></b> entries
                </div>
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <?php if ($i === (int)$page) { ?>
                            <li class="page-item active"><a href="#"
                                                            class="page-link"><?php echo htmlentities($i, ENT_QUOTES, 'UTF-8'); ?></a>
                            </li>
                        <?php } else { ?>
                            <li class="page-item"><a href="?page=<?php echo (int)$i; ?>"
                                                     class="page-link"><?php echo htmlentities($i, ENT_QUOTES, 'UTF-8'); ?></a>
                            </li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>

        </div>
    </div>
</div>
<div id="messageOutput" class="message-output"></div>

<div class="modal" id="actorModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Actor Details</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="messageOutput"></div>
                <form id="actorForm">
                    <input type="hidden" id="actor_id" name="actor_id">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required>
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveActorBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="deleteActorModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirm Deletion</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this actor?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>
