<div class="page-container">
    <h3 class="mt-5 text-center">Lấy danh sách Kinh Thánh</h3>
    <div class="mt-3 text-center">
        <div class="card">
            <h5 class="card-title">Danh sách phiên bản</h5>
            <div class="row mb-2">
                <div class="col-md-12">
                    <label for="language" style="display:block; margin-bottom: 5px; text-align:left;">Chọn ngôn ngữ:</label>
                    <select id="language" style="width:100%;">
                        <?php foreach ($languages as $item) : ?>
                            <option value="<?= htmlspecialchars($item->code) ?>"><?= htmlspecialchars($item->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                     <!-- Placeholder for alignment or another element -->
                </div>
                <div class="col-md-3" style="align-self: flex-end;">
                    <button onclick="fetchBible()" id="btnFetch">Lấy Kinh Thánh</button>
                </div>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Mã</th>
                            <th>Tên phiên bản</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="dataTable">
                        <!-- JS will populate this -->
                    </tbody>
                </table>
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
        var apiUrl = `api/fetch-bibles.php?language=${language}`;
        
        var fetchButton = document.getElementById('btnFetch');
        var originalButtonText = fetchButton.innerHTML;
        fetchButton.innerHTML = '<span class="spinner"></span> Đang tải...';
        fetchButton.disabled = true;

        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function(res) {
                console.log(res);
                var dataTableBody = document.getElementById('dataTable');
                dataTableBody.innerHTML = '';

                if (res.response && res.response.code === 200 && res.response.data && res.response.data.versions) {
                    var data = res.response.data;
                    if (data.versions.length > 0) {
                        data.versions.forEach(function(item) {
                            var row = dataTableBody.insertRow();
                            row.insertCell().textContent = item.id;
                            row.insertCell().textContent = item.abbreviation;
                            row.insertCell().textContent = item.local_title;
                            var actionCell = row.insertCell();
                            var buildButton = document.createElement('button');
                            buildButton.textContent = 'Xây dựng Sách';
                            buildButton.onclick = function() { buildBook(item.id); };
                            actionCell.appendChild(buildButton);
                        });
                    } else {
                         dataTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Không có dữ liệu</td></tr>';
                    }
                } else {
                    dataTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Lỗi khi lấy dữ liệu hoặc không tìm thấy dữ liệu.</td></tr>';
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error: ", textStatus, errorThrown);
                var dataTableBody = document.getElementById('dataTable');
                dataTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Lỗi AJAX khi kết nối máy chủ.</td></tr>';
            },
            complete: function() {
                fetchButton.innerHTML = originalButtonText;
                fetchButton.disabled = false;
            }
        });
    }

    function buildBook(id) {
        var apiUrl = `api/build-bible.php?id=${id}`;
        alert('Đang gửi yêu cầu xây dựng sách cho ID: ' + id + ". Vui lòng chờ...");

        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function(res) {
                console.log(res);
                if (res.code === 200) {
                    showToast('Thành công', 'Đã xây dựng sách thành công!', 'success');
                } else {
                    showToast('Lỗi', res.message || 'Không thể xây dựng sách.', 'error');
                }
            },
            error: function() {
                 showToast('Lỗi Máy Chủ', 'Không thể kết nối để xây dựng sách.', 'error');
            }
        });
    }

    function showToast(title, message, type) {
        alert(title + ": " + message + " (Type: " + type + ")");
    }
</script>