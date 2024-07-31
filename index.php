<?php 
    $languages = json_decode(file_get_contents('data/languages.json'));


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bible Crawl</title>
    <!-- bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- fontawesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</head>

<body>
    <main>
        <div class="container">
            <div class="row">
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
            </div>
        </div>
    </main>
    <script>
        // fetch all language
        document.addEventListener('DOMContentLoaded', function() {
            // select2
            $('#language').select2();
        });

       


        function fetchBible() {
            var language = $('#language').val();
            var apiUrl = `http://localhost:8000/api/get-bibles.php?language=${language}`;
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
                                              <button class="btn btn-primary" onclick="fetchBooks('${item.id}')">Fetch Books</button>
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
    </script>
</body>

</html>