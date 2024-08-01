<?php
$files = glob(__DIR__ . '/../data/bibles/*');
$bibles = [];
foreach ($files as $file) {
    $bible = json_decode(file_get_contents($file));
    $bibles[] = $bible;
}
$bibles = array_map('unserialize', array_unique(array_map('serialize', $bibles)));



?>
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
                        <select class="form-control" id="bible">
                            <?php foreach ($bibles as $item) : ?>
                                <option value="<?= $item->id ?>"><?= $item->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- button -->
                    <button class="btn btn-primary" onclick="fetchVerse()" id="btnFetch">Get Verse</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Book</th>
                                <th>Chapter</th>
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
    function fetchVerse() {
        var bible_id =$ ('#bible').val();
        $.ajax({
            url: 'api/build-verse.php?bible_id=' + bible_id,
            method: 'GET',
            success: function(res) {
                console.log(res);
            }
        });
    }
</script>