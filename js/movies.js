$(document).ready(function () {
    const messageOutput = $('#messageOutput');
    const movieModal = $('#movieModal');
    const deleteMovieModal = $('#deleteMovieModal');
    const importModal = $('#importModal');
    const messageImportOutput = $('#messageImportOutput');
    const movieTableBody = $("#movieTable tbody");
    const hintText = $(".hint-text");
    const pagination = $(".pagination");
    const movieTable = $("#movieTable");
    const moviesPerPage = 10;

    function showMessage(message, type = 'success') {
        messageOutput.html('<div class="alert alert-' + type + '">' + message + '</div>');
        setTimeout(function () {
            messageOutput.empty();
        }, 3000);
    }

    function showImportMessage(message, type = 'success') {
        messageImportOutput.html('<div class="alert alert-' + type + '">' + message + '</div>');
        setTimeout(function () {
            messageImportOutput.empty();
        }, 9000);
    }

    function removeModalBackdrop() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    }
    function escapeHtml(text) {
        var doc = new DOMParser().parseFromString(text, "text/html");
        return doc.documentElement.textContent;
    }

    function editMovie(movieId) {
        const actorName = $('#actor_name');
        return $.ajax({
            type: 'GET',
            url: 'movies.php',
            data: {movie_id: movieId, action: 'get_movie_data'},
            dataType: 'json',
            success: function (response) {
                if (response.status === 200) {
                    const data = response.data;
                    $('#movie_id').val(data.id);
                    $('#movie_title').val(data.title);
                    $('#release_year').val(data.release_year);
                    $('#format').val(data.format);
                    actorName.val(data.actor_ids);
                    actorName.trigger('change');
                    movieModal.modal('show');
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showMessage(error, 'danger');
            }
        });
    }

    function saveMovie(dataToSend) {
        return $.ajax({
            type: 'POST',
            url: 'movies.php',
            data: dataToSend,
            dataType: 'json',
            success: function (response) {
                if (response.status === 200) {
                    showMessage(response.message, 'success');
                    movieModal.modal('hide');
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

    function populateMovieTable(movies, totalMovies) {
        movieTableBody.empty();

        movies.forEach(function (movie) {
            var actorNames = Array.isArray(movie.actor_names) ? movie.actor_names.join(", ") : movie.actor_names.split(', ').join(", ");

            var row = '<tr>' +
                '<td>' + movie.id + '</td>' +
                '<td>' + escapeHtml(movie.title) + '</td>' +
                '<td>' + movie.release_year + '</td>' +
                '<td>' + movie.format + '</td>' +
                '<td>' + actorNames + '</td>' +
                '<td>' +
                '<a href="javascript:;" class="edit" title="Edit" data-toggle="modal" data-target="#movieModal" data-movie-id="' + movie.id + '"><i class="material-icons">&#xE254;</i></a>' +
                '<a href="javascript:;" class="delete" title="Delete" data-toggle="modal" data-target="#deleteMovieModal" data-movie-id="' + movie.id + '"><i class="material-icons">&#xE5C9;</i></a>' +
                '</td>' +
                '</tr>';

            movieTableBody.append(row);
        });

        var entriesShown = movies.length;
        if (entriesShown > 10) {
            entriesShown = 10;
        }
        hintText.html('Showing <b>' + entriesShown + '</b> out of <b>' + totalMovies + '</b> entries');

        pagination.empty();

        for (var i = 1; i <= Math.ceil(totalMovies / moviesPerPage); i++) {
            var pageLink = i === 1 ? '<li class="page-item active">' : '<li class="page-item">';
            pageLink += '<a href="?page=' + i + '" class="page-link">' + i + '</a></li>';
            pagination.append(pageLink);
        }
    }

    function deleteMovie(movieId) {
        return $.ajax({
            type: 'POST',
            url: 'movies.php',
            data: {movie_id: movieId, action: 'delete_movie'},
            dataType: 'json',
            success: function (response) {
                if (response.status === 200) {
                    showMessage(response.message, 'success');
                    deleteMovieModal.modal('hide');
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

    function searchMovie(searchValue) {
        return $.ajax({
            url: "movies.php",
            type: "POST",
            data: {action: 'search_actors', searchValue: searchValue},
            success: function (response) {
                if (response.status === 200) {
                    var movies = response.results.data;
                    var totalMovies = response.results.total_count;
                    populateMovieTable(movies, totalMovies);
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                showMessage(error, 'danger');
            }
        });
    }

    movieTable.on("click", ".edit", function () {
        const movieId = $(this).data("movie-id");
        editMovie(movieId);
    });

    movieTable.on("click", ".delete", function () {
        deleteMovieModal.modal("show");
        const movieId = $(this).data("movie-id");
        $("#deleteMovieModal .btn-danger").on("click", function () {
            deleteMovie(movieId);
        });
        removeModalBackdrop();
    });
    $('#saveMovieBtn').on('click', function () {
        var movieId = $('#movie_id').val();
        var movieTitle = $('#movie_title').val();
        var releaseYear = parseInt($('#release_year').val());
        var format = $('#format').val();
        var actorIds = $('#actor_name').val();
        movieId = movieId ? movieId : null;

        if (!movieTitle || !releaseYear || !format || actorIds.length === 0) {
            showMessage('Please fill in all the required fields.', 'danger');
            return;
        }

        if (isNaN(releaseYear)) {
            showMessage('Release year must be an integer.', 'danger');
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
        saveMovie(dataToSend);
    });

    $("#searchInput").on("input", function () {
        var searchValue = $(this).val().trim();
        if (searchValue !== "") {
            searchMovie(searchValue);
            window.history.pushState({searchValue: searchValue}, "", "?search=" + encodeURIComponent(searchValue));
        } else {
            window.history.pushState({}, "", window.location.pathname);
            location.reload();
        }
    });
    $('#closeMovieBtn').click(function () {
        movieModal.modal('hide');
        removeModalBackdrop();
    });

    movieModal.on('hidden.bs.modal', function () {
        removeModalBackdrop();
    });

    $('#importBtn').click(function (e) {
        importModal.modal('show');
    });
    $('#importModal .close').click(function () {
        importModal.modal('hide');
        location.reload();
    });
    importModal.on('click', '.btn-secondary', function () {
        importModal.modal('hide');
        location.reload();
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
                        if (response.status === 200) {
                            showImportMessage(response.message, 'success');
                            setTimeout(function () {
                                importModal.modal('hide');
                                location.reload();
                            }, 9000);
                        } else {
                            showImportMessage(response.message, 'danger');
                        }
                    },
                    error: function (error) {
                        showImportMessage(error, 'danger');
                    }
                });
            };
            reader.readAsText(file);
        } else {
            showImportMessage('File not uploaded.', 'danger');
        }
    });
    $('#exportBtn').click(function (e) {
        e.preventDefault();
        $.ajax({
            type: 'GET',
            url: 'movies.php',
            data: {action: 'export_movies'},
            xhrFields: {
                responseType: 'blob'
            },
            success: function (blobData) {
                const blob = new Blob([blobData], {type: 'text/plain'});
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'movies_data.txt';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                showMessage('Data exported successfully!', 'success');
            },
            error: function (error) {
                showMessage('Export failed!', 'danger');
            }
        });
    });
});


