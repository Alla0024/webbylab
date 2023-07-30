$(document).ready(function () {
    const messageOutput = $('#messageOutput');
    const actorModal = $('#actorModal');
    const deleteActorModal = $('#deleteActorModal');
    const actorTableBody = $("#actorTable tbody");
    const hintText = $(".hint-text");
    const pagination = $(".pagination");
    const actorsPerPage = 10;

    function showMessage(message, type = 'success') {
        messageOutput.html('<div class="alert alert-' + type + '">' + message + '</div>');
        setTimeout(function () {
            messageOutput.empty();
        }, 3000);
    }

    function removeModalBackdrop() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    }

    function editActor(actorId) {
        return $.ajax({
            type: 'GET',
            url: 'actors.php',
            data: {actor_id: actorId, action: 'get_actor_data'},
            dataType: 'json',
            success: function (response) {
                if (response.status === 200) {
                    const data = response.data;
                    $('#actor_id').val(data.id);
                    $('#first_name').val(data.first_name);
                    $('#last_name').val(data.last_name);
                    actorModal.modal('show');
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showMessage(error, 'danger');
            }
        });
    }

    function saveActor(dataToSend) {
        return $.ajax({
            type: 'POST',
            url: 'actors.php',
            data: dataToSend,
            dataType: 'json',
            success: function (response) {
                if (response.status === 200) {
                    showMessage(response.message, 'success');
                    actorModal.modal('hide');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showMessage(error, 'danger');
            }
        });
    }

    function populateActorTable(actors, totalActors) {
        actorTableBody.empty();

        actors.forEach(function (actor) {
            var row = '<tr>' +
                '<td>' + actor.id + '</td>' +
                '<td>' + actor.first_name + '</td>' +
                '<td>' + actor.last_name + '</td>' +
                '<td>' +
                '<a href="javascript:;" class="edit_actor" title="Edit" data-toggle="modal" data-target="#actorModal" data-actor-id="' + actor.id + '"><i class="material-icons">&#xE254;</i></a>' +
                '<a href="javascript:;" class="delete_actor" title="Delete" data-toggle="modal" data-target="#deleteActorModal" data-actor-id="' + actor.id + '"><i class="material-icons">&#xE5C9;</i></a>' +
                '</td>' +
                '</tr>';

            actorTableBody.append(row);
        });

        var entriesShown = actors.length;
        if (entriesShown > 10) {
            entriesShown = 10;
        }
        hintText.html('Showing <b>' + entriesShown + '</b> out of <b>' + totalActors + '</b> entries');

        pagination.empty();

        for (var i = 1; i <= Math.ceil(totalActors / actorsPerPage); i++) {
            var pageLink = i === 1 ? '<li class="page-item active">' : '<li class="page-item">';
            pageLink += '<a href="?page=' + i + '" class="page-link">' + i + '</a></li>';
            pagination.append(pageLink);
        }
    }

    function deleteActor(actorId) {
        return $.ajax({
            type: 'POST',
            url: 'actors.php',
            data: {actor_id: actorId, action: 'delete_actor'},
            dataType: 'json',
            success: function (response) {
                if (response.status === 200) {
                    showMessage(response.message, 'success');
                    deleteActorModal.modal('hide');
                    removeModalBackdrop()
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showMessage(error, 'danger');
            }
        });
    }

    function searchActor(searchValue) {
        return $.ajax({
            url: "actors.php",
            type: "POST",
            data: {action: 'search_actors', searchValue: searchValue},
            success: function (response) {
                if (response.status === 200) {
                    var actors = response.results.data;
                    var totalActors = response.results.total_count;
                    populateActorTable(actors, totalActors);
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showMessage(error, 'danger');
            }
        });
    }

    $('.edit_actor').on('click', function () {
        const actorId = $(this).data('actor-id');
        editActor(actorId);
    });
    $('.delete_actor').on('click', function () {
        deleteActorModal.modal('show');
        const actorId = $(this).data('actor-id');
        $('#deleteActorModal .btn-danger').on('click', function () {
            deleteActor(actorId);
        });
        removeModalBackdrop();
    });
    $('#saveActorBtn').on('click', function () {
        var actorId = $('#actor_id').val();
        var firstName = $('#first_name').val();
        var lastName = $('#last_name').val();
        actorId = actorId ? actorId : null;

        if (!firstName || !lastName) {
            showMessage('Please fill in all the required fields.', 'danger');
            return;
        }

        var dataToSend = {
            action: 'update_actor',
            actor_id: actorId,
            first_name: firstName,
            last_name: lastName,
        };
        saveActor(dataToSend);
    });

    $("#searchActorInput").on("input", function () {
        var searchValue = $(this).val().trim();
        if (searchValue !== "") {
            searchActor(searchValue);
            window.history.pushState({searchValue: searchValue}, "", "?action=actors&search=" + encodeURIComponent(searchValue));
        } else {
            const urlSearchParams = new URLSearchParams(window.location.search);
            urlSearchParams.delete("search");
            const newUrl = window.location.pathname + "?" + urlSearchParams.toString();
            window.history.pushState({}, "", newUrl);
            location.reload();
        }
    });
    $('#closeActorBtn').click(function () {
        actorModal.modal('hide');
        removeModalBackdrop();
    });

    actorModal.on('hidden.bs.modal', function () {
        removeModalBackdrop();
    });

    $('#addNewActorBtn').click(function (e) {
        actorModal.modal('show');
    });
    $('#actorModal .close').click(function () {
        actorModal.modal('hide');
        location.reload();
    });
    actorModal.on('click', '.btn-secondary', function () {
        actorModal.modal('hide');
        location.reload();
    });
});
