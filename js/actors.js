$(document).ready(function () {
    var editActorId = null;
    var messageOutput = $('#messageOutput');

    $('.btn-secondary[data-target="#actorModal"]').on('click', function () {
        editActorId = null;
        $('#actorForm')[0].reset();
    });

    function removeModalBackdrop() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    }

    $('#closeActorBtn').click(function () {
        $('#actorModal').modal('hide');
        removeModalBackdrop();
    });

    $('#actorModal').on('hidden.bs.modal', function () {
        removeModalBackdrop();
    });

    $('.edit_actor').on('click', function () {
        var actorId = $(this).data('actor-id');
        $.ajax({
            type: 'GET',
            url: 'actors.php',
            data: {actor_id: actorId, action: 'get_actor_data'},
            dataType: 'json',
            success: function (response) {
                $('#actor_id').val(response.id);
                $('#first_name').val(response.first_name);
                $('#last_name').val(response.last_name);
                $('#actorModal').modal('show');
            },
            error: function (error) {
                console.error('Error fetching actor data:', error.responseText);
            }
        });
    });

    $('#saveActorBtn').on('click', function () {
        var actorId = $('#actor_id').val()
        var firstName = $('#first_name').val();
        var lastName = $('#last_name').val();
        var messageOutput = $('#messageOutput');
        actorId = actorId ? actorId : null;

        if (!firstName || !lastName) {
            messageOutput.empty();
            messageOutput.html('<div class="alert alert-danger">Please fill in all the required fields.</div>');
            return;
        }

        var dataToSend = {
            action: 'update_actor',
            actor_id: actorId,
            first_name: firstName,
            last_name: lastName,
        };

        $.ajax({
            type: 'POST',
            url: 'actors.php',
            data: dataToSend,
            success: function (response) {
                messageOutput.html('<div class="alert alert-success">' + response + '</div>');
                $('#actorModal').modal('hide');
                setTimeout(function () {
                    location.reload();
                }, 3000);
            },
            error: function (error) {
                messageOutput.html('<div class="alert alert-danger">Error saving actor changes: ' + error.statusText + '</div>');
                setTimeout(function () {
                    messageOutput.empty();
                }, 3000);
            }
        });
    });

    $('.delete_actor').on('click', function () {
        var actorId = $(this).data('actor-id');
        var deleteButton = $(this);

        $('#deleteActorModal .btn-danger').on('click', function () {
            $.ajax({
                type: 'POST',
                url: 'actors.php',
                data: {actor_id: actorId, action: 'delete_actor'},
                success: function (response) {
                    messageOutput.html('<div class="alert alert-success">' + response + '</div>');
                    $('#deleteActorModal').modal('hide');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                    deleteButton.closest('tr').remove();
                },
                error: function (error) {
                    messageOutput.html('<div class="alert alert-danger">Error deleting actor: ' + error.statusText + '</div>');
                    setTimeout(function () {
                        messageOutput.empty();
                    }, 3000);
                }
            });
        });
    });

    var actorsPerPage = 10;
    $("#searchInput").on("input", function () {
        var searchValue = $(this).val().trim();
        if (searchValue !== "") {
            $.ajax({
                url: "actors.php",
                type: "POST",
                data: {action: 'search_actors', searchValue: searchValue},
                success: function (data) {
                    var response = JSON.parse(data);
                    var actors = response.actors;
                    var totalActors = response.totalActors;
                    var $tableBody = $("#actorTable tbody");
                    $tableBody.empty();

                    actors.forEach(function (actor) {
                        var row = '<tr>' +
                            '<td>' + actor.id + '</td>' +
                            '<td>' + actor.first_name + '</td>' +
                            '<td>' + actor.last_name + '</td>' +
                            '<td>' +
                            '<a href="#" class="edit_actor" title="Edit" data-toggle="modal" data-target="#actorModal" data-actor-id="' + actor.id + '"><i class="material-icons">&#xE254;</i></a>' +
                            '<a href="#" class="delete_actor" title="Delete" data-toggle="modal" data-target="#deleteActorModal" data-actor-id="' + actor.id + '"><i class="material-icons">&#xE5C9;</i></a>' +
                            '</td>' +
                            '</tr>';

                        $tableBody.append(row);
                    });
                    var $hintText = $(".hint-text");
                    var entriesShown = actors.length;

                    if (entriesShown > 10) {
                        entriesShown = 10;
                    }
                    $hintText.html('Showing <b>' + entriesShown + '</b> out of <b>' + totalActors + '</b> entries');

                    var $pagination = $(".pagination");
                    $pagination.empty();

                    for (var i = 1; i <= Math.ceil(totalActors / actorsPerPage); i++) {
                        var pageLink = i === 1 ? '<li class="page-item active">' : '<li class="page-item">';
                        pageLink += '<a href="?page=' + i + '" class="page-link">' + i + '</a></li>';
                        $pagination.append(pageLink);
                    }
                },
                error: function () {
                    $("#actorTable").html("<p>Error occurred while fetching data.</p>");
                }
            });
        } else {
            location.reload();
        }
    });
});
