$(document).ready(function () {
    var editMovieId = null;
    var messageOutput = $('#messageOutput');

    $('.btn-secondary[data-target="#movieModal"]').on('click', function () {
        editMovieId = null;
        $('#movieForm')[0].reset();
    });

    function removeModalBackdrop() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    }

    $('#closeMovieBtn').click(function () {
        $('#movieModal').modal('hide');
        removeModalBackdrop();
    });

    $('#movieModal').on('hidden.bs.modal', function () {
        removeModalBackdrop();
    });

    $('.edit').on('click', function () {
        var movieId = $(this).data('movie-id');
        var actor_name = $('#actor_name');
        $.ajax({
            type: 'GET',
            url: 'movies.php',
            data: {movie_id: movieId, action: 'get_movie_data'},
            dataType: 'json',
            success: function (response) {
                $('#movie_id').val(response.id);
                $('#movie_title').val(response.title);
                $('#release_year').val(response.release_year);
                $('#format').val(response.format);
                actor_name.val(response.actor_ids);
                actor_name.trigger('change');
                $('#movieModal').modal('show');
            },
            error: function (error) {
                console.error('Error fetching movie data:', error.responseText);
            }
        });
    });

    $('#saveMovieBtn').on('click', function () {
        var movieId = $('#movie_id').val();
        var movieTitle = $('#movie_title').val();
        var releaseYear = parseInt($('#release_year').val());
        var format = $('#format').val();
        var actorIds = $('#actor_name').val();
        var messageOutput = $('#messageOutput');
        movieId = movieId ? movieId : null;

        if (!movieTitle || !releaseYear || !format || actorIds.length === 0) {
            messageOutput.empty();
            messageOutput.html('<div class="alert alert-danger">Please fill in all the required fields.</div>');
            return;
        }

        if (isNaN(releaseYear)) {
            messageOutput.empty();
            messageOutput.html('<div class="alert alert-danger">Release year must be an integer.</div>');
            return;
        }

        var dataToSend = {
            action: 'update_movie',
            movie_id: movieId,
            movie_title: movieTitle,
            release_year: releaseYear,
            format: format,
            actor_ids: actorIds,
        };

        $.ajax({
            type: 'POST',
            url: 'movies.php',
            data: dataToSend,
            success: function (response) {
                messageOutput.html('<div class="alert alert-success">' + response + '</div>');
                $('#movieModal').modal('hide');
                setTimeout(function () {
                    location.reload();
                }, 3000);
            },
            error: function (error) {
                messageOutput.html('<div class="alert alert-danger">Error saving movie changes: ' + error.responseText + '</div>');
                setTimeout(function () {
                    messageOutput.empty();
                }, 3000);
            }
        });
    });


    $('.delete').on('click', function () {
        var movieId = $(this).data('movie-id');
        var deleteButton = $(this);

        $('#deleteMovieModal .btn-danger').on('click', function () {
            $.ajax({
                type: 'POST',
                url: 'movies.php',
                data: {movie_id: movieId, action: 'delete_movie'},
                success: function (response) {
                    messageOutput.html('<div class="alert alert-success">' + response + '</div>');
                    $('#deleteMovieModal').modal('hide');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                    deleteButton.closest('tr').remove();
                },
                error: function (error) {
                    messageOutput.html('<div class="alert alert-danger">Error deleting movie: ' + error.statusText + '</div>');
                    setTimeout(function () {
                        messageOutput.empty();
                    }, 3000);
                }
            });
        });
    });

    var moviesPerPage = 10;
    $("#searchInput").on("input", function () {
        var searchValue = $(this).val().trim();
        if (searchValue !== "") {
            $.ajax({
                url: "movies.php",
                type: "POST",
                data: {action: 'search_actors', searchValue: searchValue},
                success: function (data) {
                    var response = JSON.parse(data);
                    var movies = response.movies;
                    var totalMovies = response.totalMovies;
                    var $tableBody = $("#movieTable tbody");
                    $tableBody.empty();

                    movies.forEach(function (movie) {
                        var actorNames = Array.isArray(movie.actor_names) ? movie.actor_names.join(", ") : movie.actor_names.split(', ').join(", ");

                        var row = '<tr>' +
                            '<td>' + movie.id + '</td>' +
                            '<td>' + movie.title + '</td>' +
                            '<td>' + movie.release_year + '</td>' +
                            '<td>' + movie.format + '</td>' +
                            '<td>' + actorNames + '</td>' +
                            '<td>' +
                            '<a href="#" class="edit" title="Edit" data-toggle="modal" data-target="#movieModal" data-movie-id="' + movie.id + '"><i class="material-icons">&#xE254;</i></a>' +
                            '<a href="#" class="delete" title="Delete" data-toggle="modal" data-target="#deleteMovieModal" data-movie-id="' + movie.id + '"><i class="material-icons">&#xE5C9;</i></a>' +
                            '</td>' +
                            '</tr>';

                        $tableBody.append(row);
                    });
                    var $hintText = $(".hint-text");
                    var entriesShown = movies.length;
                    if (entriesShown > 10) {
                        entriesShown = 10;
                    }
                    $hintText.html('Showing <b>' + entriesShown + '</b> out of <b>' + totalMovies + '</b> entries');

                    var $pagination = $(".pagination");
                    $pagination.empty();

                    for (var i = 1; i <= Math.ceil(totalMovies / moviesPerPage); i++) {
                        var pageLink = i === 1 ? '<li class="page-item active">' : '<li class="page-item">';
                        pageLink += '<a href="?page=' + i + '" class="page-link">' + i + '</a></li>';
                        $pagination.append(pageLink);
                    }
                },
                error: function () {
                    $("#movieTable").html("<p>Error occurred while fetching data.</p>");
                }
            });
        } else {
            location.reload();
        }
    });
    var importModal = $('#importModal');
    var messageImportOutput = $('#messageImportOutput');

    $('#importBtn').click(function (e) {
        importModal.modal('show');
    });
    $('#importModal .close').click(function () {
        importModal.modal('hide');
    });
    importModal.on('click', '.btn-secondary', function () {
        importModal.modal('hide');
    });

    $('#importFileBtn').click(function (e) {
        e.preventDefault();
        const fileInput = $('#fileInput')[0];
        const file = fileInput.files[0];

        if (file) {
            const fileName = file.name;
            const validExtensions = ['txt'];
            const fileExtension = fileName.split('.').pop().toLowerCase();

            if (!validExtensions.includes(fileExtension)) {
                messageImportOutput.empty();
                messageImportOutput.html('<div class="alert alert-danger">Invalid file format. Please upload a txt file.</div>');
                return;
            }
            const reader = new FileReader();

            reader.onload = function (event) {
                const data = event.target.result;
                if (!data) {
                    messageImportOutput.empty();
                    messageImportOutput.html('<div class="alert alert-danger">No data available.</div>');
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: 'movies.php',
                    data: {action: 'import_movies', movie_data: data},
                    success: function (response) {
                        messageImportOutput.empty();
                        messageImportOutput.html('<div class="alert alert-success">' + response + '</div>');
                    },
                    error: function (error) {
                        messageOutput.empty();
                        messageOutput.html('<div class="alert alert-danger">Error saving movie data: ' + error.statusText + '</div>');
                    }
                });
            };

            reader.readAsText(file);
        } else {
            messageImportOutput.empty();
            messageImportOutput.html('<div class="alert alert-danger">File not uploaded.</div>');
        }
    });

});


