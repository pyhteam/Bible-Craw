<div class="col-md-12">
    <h3 class="text-center mt-5">Bible Crawl</h3>
    <div class="text-center mt-3">
        <!-- Card -->
        <div class="card">

            <div class="card-body">
                <h5 class="card-title">List Bible</h5>
                <div class="row mb-2">
                    <!-- select language -->
                    <div class="col-md-4">
                        <select class="form-control" id="language">
                            <?php foreach ($languages as $item) : ?>
                                <option value="<?= $item->code ?>"><?= $item->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- button -->
                    <button class="btn btn-primary" onclick="fetchBible()" id="btnFetch">Get Bible</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Action</th>
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
    // fetch all language
    document.addEventListener('DOMContentLoaded', function() {
        // select2
        $('#language').select2();
    });

    function fetchBible() {
        var language = $('#language').val();
        var apiUrl = `http://localhost:8000/api/fetch-bibles.php?language=${language}`;
        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function(res) {
                console.log(res);
                if (res.response.code === 200) {
                    var data = res.response.data;
                    var html = '';
                    data.versions.forEach(function(item) {
                        html += `<tr>
                                         <td>${item.id}</td>
                                         <td>${item.abbreviation}</td>
                                         <td>${item.local_title}</td>
                                         <td>
                                              <button class="btn btn-primary" onclick="buildBook(${item.id})">Build Book</button>
                                         </td>
                                        </tr>`;
                    });
                    $('#dataTable').html(html);
                    return;
                }
                $('#dataTable').html('<tr><td colspan="4">No data found</td></tr>');
            }
        });
    }

    function buildBook(id) {
        var apiUrl = `http://localhost:8000/api/build-bible.php?id=${id}`;
        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function(res) {
                console.log(res);
                if (res.code === 200) {
                    alert('Success build book');
                    return;
                }
                alert('Failed build book');
            }
        });
    }
</script>