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
        $('#dataTable').html('');
        var book_code = [];

        // Check select all
        let all = $('#book').val();
        if (all.includes('ALL')) {
            var books = JSON.parse(localStorage.getItem('books'));
            books.forEach(function(item) {
                book_code.push(item.code);
            });
            console.log(book_code);
        } else {
            $('#book option:selected').each(function() {
                book_code.push($(this).val());
            });
        }

        var verses = [];
        var completedRequests = 0;
        const totalRequests = book_code.length;

        for (let i = 0; i < totalRequests; i++) {
            // using ajax  
            $.ajax({
                url: `api/build-verse.php?bible_id=${bible_id}&book_code=${book_code[i]}`,
                method: 'GET',
                beforeSend: function() {
                    $('#btnFetch').attr('disabled', true);
                    $('.progress-bar').css('width', '0%');
                    // set text
                    $('#btnFetch').html('<span class="spinner-border spinner-border-sm"></span> <span>Loading...</span>');
                    // text progress
                    $('.progress-bar').text('0%');
                    // message
                    $('#message').text('Book code: ' + book_code[i]);
                },
                success: function(data) {
                    console.log(data);
                    completedRequests++;
                    let progress = ((completedRequests / totalRequests) * 100).toFixed(2);
                    $('.progress-bar').css('width', progress + '%');
                    $('.progress-bar').text(progress + '%');
                    if (data.length > 0) {
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
                        // get dataTable html
                        var dataTable = $('#dataTable').html();
                        // append html
                        $('#dataTable').html(dataTable + html);
                    }
                    $('#btnFetch').attr('disabled', false);
                    $('#btnFetch').text('Get Verse');
                    // message
                    if (progress == 100) {
                        $('#message').text('Completed');
                    } else {
                        $('#message').text('Book code: ' + book_code[i]);
                    }


                }
            });
        }
    }
</script>