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
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Bible id</th>
                                <th>Chapter code</th>
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
                    var html = '';
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
        var book_code = $('#book').val();
        $.ajax({
            url: `api/build-verse.php?bible_id=${bible_id}&book_code=${book_code}`,
            method: 'GET',
            beforeSend: function() {
                $('#btnFetch').attr('disabled', true);
                $('#btnFetch').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...');
            },
            success: function(res) {
                console.log(res);
                if (res) {
                    var html = '';
                    res.forEach(function(item) {
                        html += '<tr>';
                        html += '<td>' + item.bible_id + '</td>';
                        html += '<td>' + item.chapter_code + '</td>';
                        html += '<td>' + item.label + '</td>';
                        html += '<td>' + item.content + '</td>';
                        html += '</tr>';
                    });
                    $('#dataTable').html(html);
                }
                $('#btnFetch').attr('disabled', false);
                $('#btnFetch').html('Get Verse');
            }
        });
    }
</script>