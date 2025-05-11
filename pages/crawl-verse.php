<div class="page-container">
    <h3 class="mt-5 text-center">Lấy chi tiết câu Kinh Thánh</h3>
    <div class="mt-3 text-center">
        <div class="card">
            <div class="management-link text-right mb-2">
                <a href="?page=manage-bibles" class="btn-link">
                    <i class="fas fa-cog"></i> Quản lý Kinh Thánh đã tải
                </a>
            </div>
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

.progress-bar {
    height: 100%;
    background-color: var(--fluent-primary);
    width: 0;
    transition: width 0.3s;
}

.highlight-card {
    animation: highlight-pulse 1.5s ease-in-out;
    box-shadow: 0 0 20px rgba(0, 120, 215, 0.5);
}

@keyframes highlight-pulse {
    0% { box-shadow: 0 0 0 rgba(0, 120, 215, 0); }
    50% { box-shadow: 0 0 20px rgba(0, 120, 215, 0.8); }
    100% { box-shadow: 0 0 0 rgba(0, 120, 215, 0); }
}

.management-link {
    padding: 0 20px;
    text-align: right;
}

.btn-link {
    color: var(--fluent-primary);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.btn-link:hover {
    color: var(--fluent-primary-dark);
    text-decoration: underline;
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
        
        // Check for pre-selected Bible and books from the Bible management page
        checkForPreselections();
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
            // If cancelled and no running processes, ensure finalization if not already handled.
            if (isCancelled && runningProcesses === 0 && intervalId) {
                 // intervalId check ensures finalizeProcess hasn't run yet from updateStats
                finalizeProcess();
            }
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
                
                if (isCancelled) {
                    // If cancelled, and no more requests are running, finalize.
                    // intervalId check ensures finalizeProcess hasn't run from updateStats or another callback
                    if (runningProcesses === 0 && intervalId) {
                        finalizeProcess();
                    }
                    return; // Do not start new processes if cancelled.
                }

                if (isPaused) {
                    // If paused, do not start new processes.
                    // updateStats will continue to reflect the current state.
                    // If everything is done and paused, it will just wait for resume or cancel.
                    return;
                }
                
                // If not paused or cancelled, process more
                if (processingQueue.length > 0) {
                    processNextBook(); // Process next in queue
                } else if (runningProcesses === 0) {
                    // Queue is empty and this was the last running process
                    finalizeProcess();
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
            intervalId = null; // Ensure it's cleared to prevent multiple calls
        } else {
            // If intervalId is already null, finalizeProcess might have been called.
            // This can happen if cancellation occurs very close to natural completion.
            // We can add a flag to ensure it only runs once if needed, but clearing intervalId is key.
            return; 
        }
        
        const totalTime = ((performance.now() - startTime) / 1000).toFixed(1);
        
        if (isCancelled) {
            $('#message').text(`Quá trình xử lý đã bị hủy. Đã cố gắng xử lý ${totalProcessed} sách.`);
            $('#timeStats').text(`Thời gian hoạt động: ${totalTime}s`);
            // Toast for cancellation is already handled by the cancel button's event handler
        } else {
            const totalBooksProcessedActually = successCount + failCount; // Books that returned a definitive success/fail
            let messageText;

            if (totalProcessed === 0) {
                messageText = 'Không có yêu cầu nào được xử lý.';
            } else if (successCount === 0 && failCount === 0 && totalProcessed > 0) {
                 messageText = `Đã xử lý ${totalProcessed} sách. Không có dữ liệu câu nào được trả về hoặc các sách không chứa câu.`;
            }
            else {
                 messageText = `Hoàn tất ${totalProcessed} yêu cầu. ${successCount} Sách được lấy thành công, ${failCount} thất bại.`;
            }
            
            $('#message').text(messageText);
            $('#timeStats').text(`Tổng thời gian: ${totalTime}s | Tốc độ trung bình: ${(totalBooksProcessedActually > 0 && totalTime > 0 ? (totalBooksProcessedActually / (totalTime / 60)) : 0).toFixed(1)} sách/phút`);
            
            if (successCount > 0) {
                showToast('Hoàn tất', `Đã lấy thành công dữ liệu cho ${successCount} sách trong ${totalTime} giây.`, 'success');
            } else if (failCount > 0) {
                showToast('Hoàn tất với lỗi', `Xử lý ${totalProcessed} sách hoàn tất với ${failCount} lỗi và ${successCount} thành công.`, 'warning');
            } else if (totalProcessed > 0) { // Processed some, but no explicit success/fail (e.g., all returned empty arrays correctly)
                showToast('Hoàn tất', `Đã xử lý ${totalProcessed} sách. Không có dữ liệu câu nào được tìm thấy hoặc có lỗi không xác định.`, 'info');
            } else { // No books processed at all
                showToast('Thông tin', 'Không có yêu cầu nào được xử lý.', 'info');
            }
        }
        
        resetProcessUI();
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

    // Function to check for pre-selected values from Bible management
    function checkForPreselections() {
        // Check if there's a pre-selected Bible ID in sessionStorage
        const preselectedBibleId = sessionStorage.getItem('preselect_bible_id');
        const preselectedBooks = sessionStorage.getItem('preselect_books');
        
        if (preselectedBibleId) {
            // Wait for Bible options to load, then select
            const checkBibleLoaded = setInterval(function() {
                const $bibleSelect = $('#bible');
                if ($bibleSelect.find('option[value="' + preselectedBibleId + '"]').length) {
                    $bibleSelect.val(preselectedBibleId).trigger('change');
                    
                    // After Bible is selected, books will load, then select books if specified
                    if (preselectedBooks) {
                        try {
                            const bookCodes = JSON.parse(preselectedBooks);
                            
                            // Wait for books to load
                            const checkBooksLoaded = setInterval(function() {
                                const $bookSelect = $('#book');
                                const allBooksLoaded = bookCodes.every(code => 
                                    $bookSelect.find('option[value="' + code + '"]').length > 0
                                );
                                
                                if (allBooksLoaded) {
                                    $bookSelect.val(bookCodes).trigger('change');
                                    clearInterval(checkBooksLoaded);
                                    
                                    // Clear the session storage to prevent reselection on page reload
                                    sessionStorage.removeItem('preselect_bible_id');
                                    sessionStorage.removeItem('preselect_books');
                                    
                                    // Auto-scroll to the form
                                    $('html, body').animate({
                                        scrollTop: $('#bible').closest('.card').offset().top - 50
                                    }, 500);
                                    
                                    // Highlight the form
                                    $('#bible').closest('.card').addClass('highlight-card');
                                    setTimeout(function() {
                                        $('#bible').closest('.card').removeClass('highlight-card');
                                    }, 2000);
                                    
                                    // Show toast notification
                                    showToast('Sách đã chọn', 'Kinh Thánh và Sách đã được chọn từ trang quản lý', 'info');
                                }
                            }, 200);
                            
                            // Clear interval after 10 seconds to prevent endless checking
                            setTimeout(function() {
                                clearInterval(checkBooksLoaded);
                            }, 10000);
                        } catch (e) {
                            console.error('Error parsing preselected books', e);
                        }
                    }
                    
                    clearInterval(checkBibleLoaded);
                }
            }, 200);
            
            // Clear interval after 10 seconds to prevent endless checking
            setTimeout(function() {
                clearInterval(checkBibleLoaded);
            }, 10000);
        }
    }
</script>