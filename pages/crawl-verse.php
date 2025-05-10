<div class="page-container">
    <h3 class="mt-5 text-center">Lấy chi tiết câu Kinh Thánh</h3>
    <div class="mt-3 text-center">
        <div class="card">
            <h5 class="card-title">Lọc và lấy dữ liệu</h5>
            <div class="form-layout">
                <div class="form-group">
                    <label for="bible">Chọn Kinh Thánh (phiên bản):</label>
                    <select id="bible" onchange="fetchBooks()" style="width:100%;"></select>
                </div>
                <div class="form-group">
                    <label for="dynamicId">Dynamic ID (từ bible.com):</label>
                    <input type="text" id="dynamicId" placeholder="Nhập Dynamic ID (ví dụ: NCjyY7i6UBiMFe5pzz_9z)">
                </div>
                <div class="form-group">
                    <label for="book">Chọn Sách:</label>
                    <select id="book" multiple="multiple" style="width:100%;"></select>
                </div>
                <div class="form-group button-group">
                    <button onclick="fetchVerse()" id="btnFetch">Lấy Câu Kinh Thánh</button>
                </div>
            </div>

            <div class="progress-section mt-3">
                <label id="message">Trạng thái: Chưa bắt đầu</label>
                <div class="progress mb-2">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
            </div>
            
            <div class="table-responsive" style="height: 400px; overflow-y: auto; margin-top: 20px;">
                <table>
                    <thead>
                        <tr>
                            <th>ID Kinh Thánh</th>
                            <th>Mã Chương</th>
                            <th>Mã Câu</th>
                            <th>Nhãn Câu</th>
                            <th>Nội dung</th>
                        </tr>
                    </thead>
                    <tbody id="dataTable">

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<style>
.form-layout {
    display: flow;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    align-items: end;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: 500;
    text-align: left;
}

.form-group.button-group {
    justify-content: flex-end;
}

.form-group.button-group button {
    width: auto;
    align-self: flex-start;
}

.progress-section label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}
</style>

<script>
    $(document).ready(function() {
        $('#bible').select2({ placeholder: "Chọn Kinh Thánh", allowClear: true });
        $('#book').select2({
            placeholder: "Chọn Sách (có thể chọn nhiều)",
            allowClear: true,
        });
        fetchBible();
    });

    function fetchBible() {
        $.ajax({
            url: 'api/get-bibles.php',
            method: 'GET',
            beforeSend: function() {
                $('#bible').prop('disabled', true);
            },
            success: function(res) {
                if (res.success && res.data) {
                    var html = '<option value=""></option>';
                    res.data.forEach(function(item) {
                        html += '<option value="' + item.id + '">' + item.name + ' (' + item.code + ')' + '</option>';
                    });
                    $('#bible').html(html).trigger('change');
                } else {
                    showToast('Lỗi tải Kinh Thánh', res.message || 'Không thể tải danh sách Kinh Thánh.', 'error');
                }
            },
            error: function() {
                showToast('Lỗi Máy Chủ', 'Không thể kết nối để tải danh sách Kinh Thánh.', 'error');
            },
            complete: function() {
                $('#bible').prop('disabled', false);
            }
        });
    }

    function fetchBooks() {
        var bible_id = $('#bible').val();
        if (!bible_id) {
            $('#book').html('<option value="ALL">Tất cả Sách</option>').trigger('change');
            localStorage.removeItem('books');
            return;
        }
        $.ajax({
            url: `api/get-books.php?bible_id=${bible_id}`,
            method: 'GET',
            beforeSend: function() {
                $('#book').prop('disabled', true);
            },
            success: function(res) {
                if (res.success && res.data) {
                    localStorage.setItem('books', JSON.stringify(res.data));
                    var html = '<option value="ALL">Tất cả Sách</option>';
                    res.data.forEach(function(item) {
                        html += '<option value="' + item.code + '">' + item.name + '</option>';
                    });
                    $('#book').html(html).trigger('change');
                } else {
                     showToast('Lỗi tải Sách', res.message || 'Không thể tải danh sách Sách cho Kinh Thánh đã chọn.', 'error');
                     localStorage.removeItem('books');
                     $('#book').html('<option value="ALL">Tất cả Sách</option>').trigger('change');
                }
            },
            error: function() {
                showToast('Lỗi Máy Chủ', 'Không thể kết nối để tải danh sách Sách.', 'error');
                localStorage.removeItem('books');
                $('#book').html('<option value="ALL">Tất cả Sách</option>').trigger('change');
            },
            complete: function() {
                $('#book').prop('disabled', false);
            }
        });
    }

    function fetchVerse() {
        var bible_id = $('#bible').val();
        var dynamicId = $('#dynamicId').val();
        var selectedBooks = $('#book').val();

        if (!bible_id) {
            showToast('Yêu cầu', 'Vui lòng chọn một Kinh Thánh.', 'warning');
            return;
        }
        if (!dynamicId) {
            showToast('Yêu cầu', 'Vui lòng nhập Dynamic ID.', 'warning');
            return;
        }
        if (!selectedBooks || selectedBooks.length === 0) {
            showToast('Yêu cầu', 'Vui lòng chọn ít nhất một Sách.', 'warning');
            return;
        }

        document.getElementById('dataTable').innerHTML = ''; 
        var book_codes_to_fetch = [];

        if (selectedBooks.includes('ALL')) {
            var books_from_storage = JSON.parse(localStorage.getItem('books'));
            if (books_from_storage) {
                books_from_storage.forEach(function(item) {
                    book_codes_to_fetch.push(item.code);
                });
            }
        } else {
            book_codes_to_fetch = selectedBooks;
        }

        if (book_codes_to_fetch.length === 0) {
            showToast('Yêu cầu', 'Không có Sách nào để lấy dữ liệu. Có thể bạn cần chọn lại Kinh Thánh để tải danh sách Sách.', 'warning');
            return;
        }

        var totalRequests = book_codes_to_fetch.length;
        var completedRequests = 0;
        var $btnFetch = $('#btnFetch');
        var originalBtnText = $btnFetch.text();
        var $progressBar = $('.progress-bar');
        var $message = $('#message');

        $btnFetch.prop('disabled', true).html('<span class="spinner"></span> Đang xử lý...');
        $progressBar.css('width', '0%').text('0%');
        $message.text('Bắt đầu lấy ' + totalRequests + ' Sách...');
        
        var promises = book_codes_to_fetch.map(function(book_code) {
            return $.ajax({
                url: `api/build-verse.php?bible_id=${bible_id}&book_code=${book_code}&dynamic_id=${dynamicId}`,
                method: 'GET'
            }).done(function(data) {
                if (data && Array.isArray(data) && data.length > 0) {
                    var tableBody = document.getElementById('dataTable');
                    data.forEach(function(item) {
                        var row = tableBody.insertRow();
                        row.insertCell().textContent = item.bible_id;
                        row.insertCell().textContent = item.chapter_code;
                        row.insertCell().textContent = item.verse_code;
                        row.insertCell().textContent = item.label;
                        row.insertCell().textContent = item.content;
                    });
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error(`Lỗi khi lấy Sách ${book_code}: ${textStatus}`, errorThrown);
                showToast('Lỗi Sách '+book_code, `Không thể lấy dữ liệu cho sách ${book_code}.`, 'error');
            }).always(function() {
                completedRequests++;
                let progress = totalRequests > 0 ? ((completedRequests / totalRequests) * 100).toFixed(2) : 0;
                $progressBar.css('width', progress + '%').text(progress + '%');
                $message.text(`Đã xử lý ${completedRequests} / ${totalRequests} Sách. Sách vừa xong: ${book_code}`);
            });
        });

        Promise.allSettled(promises).then(function(results) {
            $btnFetch.prop('disabled', false).text(originalBtnText);
            let successful_fetches = results.filter(r => r.status === 'fulfilled' && r.value && r.value.length > 0).length;
            $message.text(`Hoàn tất ${totalRequests} yêu cầu. ${successful_fetches} Sách được lấy thành công.`);
            if (completedRequests === totalRequests) {
                if (successful_fetches > 0) {
                    showToast('Hoàn tất', `Đã lấy thành công dữ liệu cho ${successful_fetches} sách.`, 'success');
                } else if (totalRequests > 0) {
                    showToast('Hoàn tất với lỗi', `Không thể lấy dữ liệu cho bất kỳ sách nào trong số ${totalRequests} yêu cầu.`, 'warning');
                } else {
                    showToast('Thông tin', 'Không có yêu cầu nào được xử lý.', 'info');
                }
            }
        });
    }

    function showToast(title, message, type) {
        console.log(`Toast: [${type}] ${title} - ${message}`);
        alert(title + ": " + message);
    }
</script>