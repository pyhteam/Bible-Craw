<div class="page-container">
    <h3 class="mt-5 text-center">Lấy chi tiết câu Kinh Thánh</h3>
    <div class="mt-3 text-center">
        <div class="card">
            <h5 class="card-title">Lọc và lấy dữ liệu</h5>
            <div class="form-layout">
                <div class="form-group">
                    <label for="bible">Chọn Kinh Thánh (phiên bản):</label>
                    <select id="bible" onchange="fetchBooks()"></select>
                </div>
                <div class="form-group">
                    <label for="dynamicId">Dynamic ID (từ bible.com):</label>
                    <input type="text" id="dynamicId" placeholder="Nhập Dynamic ID (ví dụ: NCjyY7i6UBiMFe5pzz_9z)">
                </div>
                <div class="form-group">
                    <label for="book">Chọn Sách:</label>
                    <select id="book" multiple="multiple"></select>
                </div>
                <div class="form-group">
                    <label for="concurrentRequests">Số luồng xử lý đồng thời:</label>
                    <input type="number" id="concurrentRequests" min="1" max="20" value="10" class="form-control-md">
                </div>
                <div class="action-buttons">
                    <button onclick="fetchVerse()" id="btnFetch" class="form-control-md">Lấy Câu Kinh Thánh</button>
                    <button id="btnPause" class="form-control-md" disabled>Tạm dừng</button>
                    <button id="btnCancel" class="form-control-md" disabled>Hủy</button>
                </div>
            </div>

            <div class="progress-section mt-3">
                <div class="progress-stats">
                    <label id="message">Trạng thái: Chưa bắt đầu</label>
                    <span id="timeStats"></span>
                </div>
                <div class="progress mb-2">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
            </div>
            
            <div class="table-controls mt-4">
                <div class="search-box">
                    <input type="text" id="tableSearch" placeholder="Tìm kiếm..." class="form-control-md">
                </div>
                <div class="pagination-controls">
                    <button id="prevPage" class="btn-sm" disabled>Trang trước</button>
                    <span id="pageInfo">Trang 0 / 0</span>
                    <button id="nextPage" class="btn-sm" disabled>Trang sau</button>
                </div>
            </div>
            
            <div class="table-responsive" style="height: 400px; overflow-y: auto; margin-top: 10px;">
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
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.action-buttons button {
    flex: 1;
}

#btnPause {
    background-color: var(--fluent-warning);
}

#btnPause.resume {
    background-color: var(--fluent-success);
}

#btnCancel {
    background-color: var(--fluent-error);
}

.progress-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.progress-stats span {
    font-size: 14px;
    color: var(--fluent-neutral-gray);
}

.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.search-box {
    width: 250px;
}

.search-box input {
    width: 100%;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pagination-controls button {
    background-color: var(--fluent-primary);
    color: white;
    border: none;
    border-radius: 2px;
    padding: 5px 10px;
    cursor: pointer;
}

.pagination-controls button:disabled {
    background-color: #c8c6c4;
    cursor: not-allowed;
}

#pageInfo {
    font-size: 14px;
    color: var(--fluent-dark-gray);
    min-width: 80px;
    text-align: center;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 14px;
}
</style>

<script>
    // Global variables for verse data and pagination
    let allVerseData = [];
    let currentPage = 1;
    let itemsPerPage = 50;
    let totalPages = 0;
    let searchTerm = '';
    let startTime = null;
    let processingQueue = [];
    let runningProcesses = 0;
    let maxConcurrentProcesses = 10;
    let isPaused = false;
    let isCancelled = false;
    let pendingAjaxRequests = [];
    let intervalId = null;
    
    // Stats tracking
    let successCount = 0;
    let failCount = 0;
    let totalProcessed = 0;
    let lastUpdateTime = 0;

    $(document).ready(function() {
        $('#bible').select2({ placeholder: "Chọn Kinh Thánh", allowClear: true });
        $('#book').select2({
            placeholder: "Chọn Sách (có thể chọn nhiều)",
            allowClear: true,
        });
        
        // Initialize event listeners for table controls
        initTableControls();
        
        // Initialize process control buttons
        initProcessControls();
        
        // Load bibles
        fetchBible();
    });

    function initTableControls() {
        // Search functionality
        $('#tableSearch').on('input', function() {
            searchTerm = $(this).val().toLowerCase();
            currentPage = 1;
            renderTable();
        });

        // Pagination controls
        $('#prevPage').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });

        $('#nextPage').on('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        });

        // Monitor concurrent requests setting
        $('#concurrentRequests').on('change', function() {
            const value = parseInt($(this).val());
            if (value >= 1 && value <= 20) {
                maxConcurrentProcesses = value;
            } else {
                $(this).val(10);
                maxConcurrentProcesses = 10;
            }
        });
    }
    
    function initProcessControls() {
        // Pause/Resume button
        $('#btnPause').on('click', function() {
            if (isPaused) {
                // Resume processing
                isPaused = false;
                $(this).text('Tạm dừng').removeClass('resume');
                showToast('Tiếp tục', 'Tiếp tục xử lý các sách còn lại.', 'info');
                
                // Refill the processing queue with runnable tasks
                startProcessing();
            } else {
                // Pause processing
                isPaused = true;
                $(this).text('Tiếp tục').addClass('resume');
                showToast('Tạm dừng', 'Đã tạm dừng quá trình xử lý. Các yêu cầu đang chạy sẽ tiếp tục hoàn thành.', 'warning');
            }
        });
        
        // Cancel button
        $('#btnCancel').on('click', function() {
            if (confirm('Bạn có chắc chắn muốn hủy quá trình xử lý không?')) {
                isCancelled = true;
                
                // Abort all pending AJAX requests
                pendingAjaxRequests.forEach(request => {
                    if (request && request.abort) {
                        request.abort();
                    }
                });
                
                // Clear the queue
                processingQueue = [];
                
                // Clear the update interval
                if (intervalId) {
                    clearInterval(intervalId);
                    intervalId = null;
                }
                
                // Reset UI
                resetProcessUI();
                
                showToast('Đã hủy', 'Quá trình xử lý đã bị hủy bỏ.', 'error');
            }
        });
    }

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
        maxConcurrentProcesses = parseInt($('#concurrentRequests').val()) || 10;

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

        // Reset data and UI
        allVerseData = [];
        currentPage = 1;
        searchTerm = '';
        $('#tableSearch').val('');
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

        // Initialize processing state
        resetStats();
        
        // Enable control buttons
        $('#btnPause').prop('disabled', false).text('Tạm dừng').removeClass('resume');
        $('#btnCancel').prop('disabled', false);
        $('#btnFetch').prop('disabled', true).html('<span class="spinner"></span> Đang xử lý...');
        
        // Update UI
        const $progressBar = $('.progress-bar');
        const $message = $('#message');
        
        $progressBar.css('width', '0%').text('0%');
        $message.text('Bắt đầu lấy ' + book_codes_to_fetch.length + ' Sách...');
        
        // Initialize processing queue
        processingQueue = [...book_codes_to_fetch];
        isPaused = false;
        isCancelled = false;
        
        // Setup regular updates
        startTime = performance.now();
        lastUpdateTime = startTime;
        
        if (intervalId) {
            clearInterval(intervalId);
        }
        
        intervalId = setInterval(updateStats, 500);
        
        // Start processing
        startProcessing();
    }
    
    function resetStats() {
        successCount = 0;
        failCount = 0;
        totalProcessed = 0;
        runningProcesses = 0;
        pendingAjaxRequests = [];
    }
    
    function startProcessing() {
        // Launch as many parallel processes as allowed by maxConcurrentProcesses
        while (runningProcesses < maxConcurrentProcesses && processingQueue.length > 0 && !isPaused && !isCancelled) {
            processNextBook();
        }
    }
    
    function processNextBook() {
        if (processingQueue.length === 0 || isPaused || isCancelled) {
            return;
        }
        
        runningProcesses++;
        const book_code = processingQueue.shift();
        
        // Get parameters
        const bible_id = $('#bible').val();
        const dynamicId = $('#dynamicId').val();
        
        // Create AJAX request
        const ajaxRequest = $.ajax({
            url: `api/build-verse.php?bible_id=${bible_id}&book_code=${book_code}&dynamic_id=${dynamicId}&concurrent=${maxConcurrentProcesses}`,
            method: 'GET',
            success: function(data) {
                if (data && Array.isArray(data) && data.length > 0) {
                    // Add to our data array for pagination
                    allVerseData = allVerseData.concat(data);
                    
                    // Re-render the table with new data
                    renderTable();
                    successCount++;
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (textStatus !== 'abort') { // Don't count aborted requests as failures
                    console.error(`Lỗi khi lấy Sách ${book_code}: ${textStatus}`, errorThrown);
                    showToast('Lỗi Sách '+book_code, `Không thể lấy dữ liệu cho sách ${book_code}.`, 'error');
                    failCount++;
                }
            },
            complete: function() {
                runningProcesses--;
                totalProcessed++;
                
                // Remove from pending requests
                const index = pendingAjaxRequests.indexOf(ajaxRequest);
                if (index > -1) {
                    pendingAjaxRequests.splice(index, 1);
                }
                
                // If not paused or cancelled, process more
                if (!isPaused && !isCancelled) {
                    // Process next book if available
                    if (processingQueue.length > 0) {
                        processNextBook();
                    } else if (runningProcesses === 0) {
                        // All processing completed
                        finalizeProcess();
                    }
                }
            }
        });
        
        // Store the request for potential cancellation
        pendingAjaxRequests.push(ajaxRequest);
    }
    
    function updateStats() {
        if (isCancelled) return;
        
        const now = performance.now();
        const elapsedTime = ((now - startTime) / 1000).toFixed(1);
        const totalBooks = totalProcessed + processingQueue.length + runningProcesses;
        const progress = totalBooks > 0 ? ((totalProcessed / totalBooks) * 100).toFixed(1) : 0;
        
        // Update progress bar
        $('.progress-bar').css('width', progress + '%').text(progress + '%');
        
        // Calculate rates and estimates
        const processingRate = totalProcessed / (elapsedTime / 60); // books per minute
        const remainingBooks = processingQueue.length + runningProcesses;
        const estimatedRemainingTime = processingRate > 0 ? (remainingBooks / processingRate * 60).toFixed(1) : '?';
        
        // Update messages
        $('#message').text(`Đã xử lý ${totalProcessed}/${totalBooks} Sách. Thành công: ${successCount}, Lỗi: ${failCount}`);
        $('#timeStats').text(`Thời gian: ${elapsedTime}s | Tốc độ: ${processingRate.toFixed(1)} sách/phút | Còn lại: ~${estimatedRemainingTime}s`);
        
        // Check if process is complete
        if (processingQueue.length === 0 && runningProcesses === 0 && totalProcessed > 0) {
            finalizeProcess();
        }
    }
    
    function finalizeProcess() {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
        
        const totalTime = ((performance.now() - startTime) / 1000).toFixed(1);
        const totalBooks = successCount + failCount;
        
        // Update final stats
        $('#message').text(`Hoàn tất ${totalBooks} yêu cầu. ${successCount} Sách được lấy thành công, ${failCount} thất bại.`);
        $('#timeStats').text(`Tổng thời gian: ${totalTime}s | Tốc độ trung bình: ${(totalBooks / (totalTime / 60)).toFixed(1)} sách/phút`);
        
        // Reset UI
        resetProcessUI();
        
        // Show completion toast
        if (successCount > 0) {
            showToast('Hoàn tất', `Đã lấy thành công dữ liệu cho ${successCount} sách trong ${totalTime} giây.`, 'success');
        } else if (totalBooks > 0) {
            showToast('Hoàn tất với lỗi', `Không thể lấy dữ liệu cho bất kỳ sách nào trong số ${totalBooks} yêu cầu.`, 'warning');
        } else {
            showToast('Thông tin', 'Không có yêu cầu nào được xử lý.', 'info');
        }
    }
    
    function resetProcessUI() {
        $('#btnFetch').prop('disabled', false).text('Lấy Câu Kinh Thánh');
        $('#btnPause').prop('disabled', true).text('Tạm dừng').removeClass('resume');
        $('#btnCancel').prop('disabled', true);
    }
    
    function renderTable() {
        // Filter data by search term if any
        let filteredData = allVerseData;
        if (searchTerm) {
            filteredData = allVerseData.filter(item => 
                (item.bible_id && item.bible_id.toString().toLowerCase().includes(searchTerm)) ||
                (item.chapter_code && item.chapter_code.toString().toLowerCase().includes(searchTerm)) ||
                (item.verse_code && item.verse_code.toString().toLowerCase().includes(searchTerm)) ||
                (item.label && item.label.toLowerCase().includes(searchTerm)) ||
                (item.content && item.content.toLowerCase().includes(searchTerm))
            );
        }
        
        // Calculate pagination
        totalPages = Math.ceil(filteredData.length / itemsPerPage);
        
        // Adjust current page if needed
        if (currentPage > totalPages) {
            currentPage = totalPages || 1;
        }
        
        // Calculate slice indices
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        
        // Get current page data
        const currentPageData = filteredData.slice(startIndex, endIndex);
        
        // Render the table
        const tableBody = document.getElementById('dataTable');
        tableBody.innerHTML = '';
        
        if (currentPageData.length === 0) {
            const row = tableBody.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 5;
            cell.textContent = 'Không có dữ liệu để hiển thị';
            cell.style.textAlign = 'center';
            cell.style.padding = '20px';
        } else {
            currentPageData.forEach(item => {
                const row = tableBody.insertRow();
                row.insertCell().textContent = item.bible_id;
                row.insertCell().textContent = item.chapter_code;
                row.insertCell().textContent = item.verse_code;
                row.insertCell().textContent = item.label;
                const contentCell = row.insertCell();
                contentCell.textContent = item.content;
                contentCell.style.maxWidth = '400px';
                contentCell.style.overflow = 'hidden';
                contentCell.style.textOverflow = 'ellipsis';
                contentCell.style.whiteSpace = 'nowrap';
            });
        }
        
        // Update pagination controls
        $('#pageInfo').text(`Trang ${currentPage} / ${totalPages}`);
        $('#prevPage').prop('disabled', currentPage <= 1);
        $('#nextPage').prop('disabled', currentPage >= totalPages);
    }

    function showToast(title, message, type = 'info') {
        // Use the global showToast function if available
        if (window.showToast) {
            window.showToast(title, message, type);
        } else {
            console.log(`${type.toUpperCase()}: ${title} - ${message}`);
            alert(`${title}: ${message}`);
        }
    }
</script>