<div class="col-md-12">
    <h3 class="text-center mt-5">Bible Crawl</h3>
    <div class="text-center mt-3">
        <!-- Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">List Verse</h5>
                <div class="row mb-2">
                    <!-- select bible -->
                    <div class="col-md-4">
                        <select class="form-control" id="bible" onchange="fetchBooks()">
                        </select>
                    </div>
                    <!-- select books -->
                    <div class="col-md-4">
                        <select class="form-control" id="book">
                        </select>
                    </div>
                    <!-- Dynamic ID input -->
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="dynamicId" placeholder="Enter Dynamic ID">
                    </div>
                    <!-- button -->
                    <button class="btn btn-primary" onclick="fetchVerse()" id="btnFetch">Get Verse</button>
                </div>
                <!-- process bar -->
                <div class="progress mb-2">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <!-- message -->
                <div class="alert alert-info" role="alert">
                    <strong>Fetching: </strong> <span id="message"></span>
                </div>
                <div class="table-responsive" style="height: 50rem;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Bible id</th>
                                <th>Chapter code</th>
                                <th>Verse Code</th>
                                <th>Verse</th>
                                <th>Text</th>
                            </tr>
                        </thead>
                        <tbody id="dataTable">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        //select 2
        $('#bible').select2();

        // mutiple select
        // item select all
        $('#book').select2({
            placeholder: "Select Books",
            allowClear: true,
            multiple: true

        });

        fetchBible();
    });
    // get bible
    function fetchBible() {
        $.ajax({
            url: 'api/get-bibles.php',
            method: 'GET',
            beforeSend: function() {
                $('#bible').attr('disabled', true);
            },
            success: function(res) {
                if (res.success) {
                    var html = '';
                    html = '<option value="">Select Bible</option>';
                    res.data.forEach(function(item) {
                        html += '<option value="' + item.id + '">' + item.name + '</option>';
                    });
                    $('#bible').html(html);
                }
                $('#bible').attr('disabled', false);
            }
        });
    }
    // get books
    function fetchBooks() {
        var bible_id = $('#bible').val();
        $.ajax({
            url: `api/get-books.php?bible_id=${bible_id}`,
            method: 'GET',
            beforeSend: function() {
                $('#book').attr('disabled', true);
            },
            success: function(res) {

                if (res.success) {
                    // save all books to local storage
                    localStorage.setItem('books', JSON.stringify(res.data));

                    var html = '';
                    html = '<option value="ALL">All</option>';
                    res.data.forEach(function(item) {
                        html += '<option value="' + item.code + '">' + item.name + '</option>';
                    });
                    $('#book').html(html);
                }
                $('#book').attr('disabled', false);
            }
        });
    }

    function fetchVerse() {
        var bible_id = $('#bible').val();
        var dynamicId = $('#dynamicId').val(); // Get the dynamic ID

        if (!bible_id) {
            alert('Please select a Bible.');
            return;
        }
        if (!dynamicId) {
            alert('Please enter the Dynamic ID.');
            return;
        }

        $('#dataTable').html(''); // Clear previous results
        var book_codes_to_fetch = [];

        // Check select all
        let all_selected_books = $('#book').val();
        if (all_selected_books && all_selected_books.includes('ALL')) {
            var books_from_storage = JSON.parse(localStorage.getItem('books'));
            if (books_from_storage) {
                books_from_storage.forEach(function(item) {
                    book_codes_to_fetch.push(item.code);
                });
            }
        } else if (all_selected_books) {
            all_selected_books.forEach(function(book_code) {
                book_codes_to_fetch.push(book_code);
            });
        }

        if (book_codes_to_fetch.length === 0) {
            alert('Please select at least one book.');
            return;
        }

        var totalRequests = book_codes_to_fetch.length;
        var completedRequests = 0;

        $('#btnFetch').attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> <span>Loading...</span>');
        $('.progress-bar').css('width', '0%').text('0%');
        $('#message').text('Starting to fetch ' + totalRequests + ' book(s)...');
        
        var promises = book_codes_to_fetch.map(function(book_code) {
            return $.ajax({
                url: `api/build-verse.php?bible_id=${bible_id}&book_code=${book_code}&dynamic_id=${dynamicId}`,
                method: 'GET',
                beforeSend: function() {
                    // Message could be updated here per-request if desired, but global progress is main focus
                    // $('#message').text('Fetching book code: ' + book_code); 
                }
            }).done(function(data) {
                if (data && data.length > 0) {
                    var html = '';
                    data.forEach(function(item) {
                        html += '<tr>';
                        html += '<td>' + item.bible_id + '</td>';
                        html += '<td>' + item.chapter_code + '</td>';
                        html += '<td>' + item.verse_code + '</td>';
                        html += '<td>' + item.label + '</td>';
                        html += '<td>' + item.content + '</td>';
                        html += '</tr>';
                    });
                    // Append results as they come in
                    $('#dataTable').append(html);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error(`Failed to fetch book ${book_code}: ${textStatus}`, errorThrown);
                $('#dataTable').append(`<tr><td colspan="5">Error fetching book ${book_code}: ${textStatus}</td></tr>`);
            }).always(function() {
                completedRequests++;
                let progress = ((completedRequests / totalRequests) * 100).toFixed(2);
                $('.progress-bar').css('width', progress + '%').text(progress + '%');
                $('#message').text(`Fetched ${completedRequests} of ${totalRequests} books. Last processed: ${book_code}`);
            });
        });

        Promise.allSettled(promises).then(function(results) {
            $('#btnFetch').attr('disabled', false).text('Get Verse');
            let successful_fetches = results.filter(r => r.status === 'fulfilled' && r.value && r.value.length > 0).length;
            $('#message').text(`All ${totalRequests} book requests processed. ${successful_fetches} fetched successfully.`);
            if (completedRequests === totalRequests) {
                 // Final check
            }
        });
    }
</script>