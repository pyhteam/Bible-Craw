<div class="page-container">
    <h3 class="mt-5 text-center">Quản lý Kinh Thánh đã tải</h3>
    
    <div class="mt-3">
        <div class="card">
            <div class="filter-controls mb-3">
                <div class="search-box">
                    <input type="text" id="bibleSearch" placeholder="Tìm kiếm Kinh Thánh..." class="form-control">
                </div>
                <div class="refresh-button">
                    <button id="refreshAll" class="btn-primary">
                        <i class="fas fa-sync-alt"></i> Làm mới tất cả
                    </button>
                </div>
            </div>
            
            <div class="bible-stats-summary mb-3">
                <div class="stat-box">
                    <span id="totalBibles">0</span>
                    <label>Kinh Thánh</label>
                </div>
                <div class="stat-box">
                    <span id="totalBooks">0</span>
                    <label>Sách</label>
                </div>
                <div class="stat-box">
                    <span id="totalVerses">0</span>
                    <label>Câu</label>
                </div>
                <div class="stat-box">
                    <span id="totalSize">0 MB</span>
                    <label>Dữ liệu</label>
                </div>
            </div>
            
            <div class="data-table-container">
                <table id="biblesTable">
                    <thead>
                        <tr>
                            <th>Tên Kinh Thánh</th>
                            <th>Mã</th>
                            <th>Ngôn ngữ</th>
                            <th>Số sách</th>
                            <th>Tổng câu</th>
                            <th>Kích thước</th>
                            <th>Cập nhật</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="biblesTableBody">
                        <tr>
                            <td colspan="8" class="text-center">Đang tải dữ liệu...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Bible Details Modal -->
            <div id="bibleDetailsModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="modalBibleTitle">Chi tiết Kinh Thánh</h4>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div class="bible-info-panel">
                            <div class="bible-header">
                                <h5 id="bibleDetailName"></h5>
                                <span id="bibleDetailLanguage" class="badge"></span>
                                <span id="bibleDetailStats"></span>
                            </div>
                            
                            <div class="books-filter-section">
                                <div class="search-box">
                                    <input type="text" id="bookSearch" placeholder="Tìm kiếm sách..." class="form-control">
                                </div>
                                <div class="book-type-filter">
                                    <select id="bookTypeFilter" class="form-control">
                                        <option value="all">Tất cả sách</option>
                                        <option value="available">Sách đã tải</option>
                                        <option value="missing">Sách chưa tải</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="books-grid" id="booksGrid">
                                <!-- Books will be populated here -->
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button id="downloadMissingBooks" class="btn-success">Tải sách còn thiếu</button>
                            <button id="refreshAllBooks" class="btn-primary">Làm mới tất cả sách</button>
                            <button id="exportBibleData" class="btn-info">Xuất dữ liệu</button>
                            <button id="deleteBibleData" class="btn-danger">Xóa dữ liệu</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Confirmation Modal -->
            <div id="confirmModal" class="modal">
                <div class="modal-content modal-sm">
                    <div class="modal-header">
                        <h4 id="confirmTitle">Xác nhận</h4>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p id="confirmMessage">Bạn có chắc chắn muốn thực hiện hành động này?</p>
                        <div class="action-buttons">
                            <button id="confirmYes" class="btn-danger">Đồng ý</button>
                            <button id="confirmNo" class="btn-secondary">Hủy bỏ</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.filter-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
}

.search-box {
    width: 300px;
}

.bible-stats-summary {
    display: flex;
    justify-content: space-around;
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 5px;
}

.stat-box {
    text-align: center;
}

.stat-box span {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: var(--fluent-primary);
}

.stat-box label {
    font-size: 14px;
    color: var(--fluent-dark-gray);
}

.data-table-container {
    overflow-x: auto;
}

#biblesTable {
    width: 100%;
    border-collapse: collapse;
}

#biblesTable th, #biblesTable td {
    padding: 10px;
    border: 1px solid #ddd;
}

#biblesTable th {
    background-color: #f2f2f2;
    position: sticky;
    top: 0;
}

#biblesTable tbody tr:hover {
    background-color: #f5f5f5;
    cursor: pointer;
}

.table-actions {
    display: flex;
    gap: 5px;
}

.table-actions button {
    border: none;
    padding: 5px 10px;
    border-radius: 3px;
    cursor: pointer;
}

.btn-view {
    background-color: var(--fluent-primary);
    color: white;
}

.btn-delete {
    background-color: var(--fluent-error);
    color: white;
}

.btn-export {
    background-color: var(--fluent-success);
    color: white;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 80%;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-radius: 5px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.modal-sm {
    width: 400px;
}

.modal-header {
    padding: 15px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 15px;
    overflow-y: auto;
}

.close-modal {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: black;
}

.bible-info-panel {
    margin-bottom: 20px;
}

.bible-header {
    margin-bottom: 15px;
}

.bible-header h5 {
    margin-bottom: 5px;
}

.badge {
    background-color: var(--fluent-primary);
    color: white;
    padding: 3px 8px;
    border-radius: 10px;
    font-size: 12px;
}

.books-filter-section {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
    margin-top: 15px;
}

.book-item {
    padding: 10px;
    border-radius: 5px;
    text-align: center;
    cursor: pointer;
}

.book-available {
    background-color: #e6f7ff;
    border: 1px solid #91d5ff;
}

.book-missing {
    background-color: #fff2e8;
    border: 1px solid #ffccc7;
}

.action-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.action-buttons button {
    padding: 8px 15px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    color: white;
}

.btn-success {
    background-color: var(--fluent-success);
}

.btn-primary {
    background-color: var(--fluent-primary);
}

.btn-info {
    background-color: var(--fluent-info);
}

.btn-danger {
    background-color: var(--fluent-error);
}

.btn-secondary {
    background-color: var(--fluent-neutral-gray);
}

.progress-indicator {
    width: 100%;
    height: 5px;
    background-color: #f0f0f0;
    margin-top: 5px;
    position: relative;
}

.progress-bar {
    height: 100%;
    background-color: var(--fluent-primary);
    width: 0;
    transition: width 0.3s;
}
</style>

<script>
$(document).ready(function() {
    // Initialize page
    loadBiblesData();
    
    // Event listeners
    $('#bibleSearch').on('input', filterBibles);
    $('#refreshAll').on('click', refreshAllBibles);
    
    // Modal event listeners
    $('.close-modal').on('click', closeModals);
    $(window).on('click', function(event) {
        if ($(event.target).hasClass('modal')) {
            closeModals();
        }
    });
    
    // Bible details modal events
    $('#bookSearch').on('input', filterBooks);
    $('#bookTypeFilter').on('change', filterBooks);
    $('#downloadMissingBooks').on('click', downloadMissingBooks);
    $('#refreshAllBooks').on('click', refreshSelectedBibleBooks);
    $('#exportBibleData').on('click', exportBibleData);
    $('#deleteBibleData').on('click', confirmDeleteBible);
    
    // Confirmation modal events
    $('#confirmYes').on('click', handleConfirmAction);
    $('#confirmNo').on('click', closeModals);
});

// Global variables
let allBiblesData = [];
let currentBibleDetails = null;
let confirmationCallback = null;

// Load all Bibles data
function loadBiblesData() {
    $.ajax({
        url: 'api/manage-bibles.php?action=list',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                allBiblesData = response.data;
                renderBiblesTable();
                updateSummaryStats();
            } else {
                showToast('Lỗi', response.message || 'Không thể tải dữ liệu Kinh Thánh.', 'error');
            }
        },
        error: function() {
            showToast('Lỗi kết nối', 'Không thể kết nối đến máy chủ.', 'error');
            $('#biblesTableBody').html('<tr><td colspan="8" class="text-center">Lỗi tải dữ liệu</td></tr>');
        }
    });
}

// Render the Bibles table
function renderBiblesTable() {
    if (!allBiblesData || allBiblesData.length === 0) {
        $('#biblesTableBody').html('<tr><td colspan="8" class="text-center">Không có dữ liệu Kinh Thánh</td></tr>');
        return;
    }
    
    let html = '';
    allBiblesData.forEach(function(bible) {
        html += `
            <tr data-id="${bible.id}" data-code="${bible.code}">
                <td>${bible.name}</td>
                <td>${bible.code}</td>
                <td>${bible.language_name || bible.language_code || 'N/A'}</td>
                <td>${bible.book_count || 0}</td>
                <td>${bible.verse_count || 0}</td>
                <td>${formatSize(bible.size_bytes || 0)}</td>
                <td>${formatDate(bible.last_updated)}</td>
                <td>
                    <div class="table-actions">
                        <button class="btn-view" onclick="viewBibleDetails('${bible.id}', '${bible.code}')">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-export" onclick="exportSingleBible('${bible.id}', '${bible.code}')">
                            <i class="fas fa-file-export"></i>
                        </button>
                        <button class="btn-delete" onclick="confirmDeleteSingleBible('${bible.id}', '${bible.code}')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    $('#biblesTableBody').html(html);
    
    // Add row click event for viewing details
    $('#biblesTableBody tr').on('click', function(e) {
        if (!$(e.target).closest('button').length) { // Ignore if clicked on a button
            const bibleId = $(this).data('id');
            const bibleCode = $(this).data('code');
            viewBibleDetails(bibleId, bibleCode);
        }
    });
}

// Update summary statistics
function updateSummaryStats() {
    let totalBibles = allBiblesData.length;
    let totalBooks = 0;
    let totalVerses = 0;
    let totalSizeBytes = 0;
    
    allBiblesData.forEach(function(bible) {
        totalBooks += parseInt(bible.book_count || 0);
        totalVerses += parseInt(bible.verse_count || 0);
        totalSizeBytes += parseInt(bible.size_bytes || 0);
    });
    
    $('#totalBibles').text(totalBibles);
    $('#totalBooks').text(totalBooks);
    $('#totalVerses').text(totalVerses);
    $('#totalSize').text(formatSize(totalSizeBytes));
}

// Filter Bibles based on search
function filterBibles() {
    const searchTerm = $('#bibleSearch').val().toLowerCase();
    
    if (!searchTerm) {
        renderBiblesTable();
        return;
    }
    
    const filteredBibles = allBiblesData.filter(function(bible) {
        return bible.name.toLowerCase().includes(searchTerm) || 
               bible.code.toLowerCase().includes(searchTerm) ||
               (bible.language_name && bible.language_name.toLowerCase().includes(searchTerm));
    });
    
    let html = '';
    if (filteredBibles.length === 0) {
        html = '<tr><td colspan="8" class="text-center">Không tìm thấy Kinh Thánh phù hợp</td></tr>';
    } else {
        filteredBibles.forEach(function(bible) {
            html += `
                <tr data-id="${bible.id}" data-code="${bible.code}">
                    <td>${bible.name}</td>
                    <td>${bible.code}</td>
                    <td>${bible.language_name || bible.language_code || 'N/A'}</td>
                    <td>${bible.book_count || 0}</td>
                    <td>${bible.verse_count || 0}</td>
                    <td>${formatSize(bible.size_bytes || 0)}</td>
                    <td>${formatDate(bible.last_updated)}</td>
                    <td>
                        <div class="table-actions">
                            <button class="btn-view" onclick="viewBibleDetails('${bible.id}', '${bible.code}')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-export" onclick="exportSingleBible('${bible.id}', '${bible.code}')">
                                <i class="fas fa-file-export"></i>
                            </button>
                            <button class="btn-delete" onclick="confirmDeleteSingleBible('${bible.id}', '${bible.code}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#biblesTableBody').html(html);
    
    // Add row click event for viewing details
    $('#biblesTableBody tr').on('click', function(e) {
        if (!$(e.target).closest('button').length) { // Ignore if clicked on a button
            const bibleId = $(this).data('id');
            const bibleCode = $(this).data('code');
            viewBibleDetails(bibleId, bibleCode);
        }
    });
}

// View Bible details
function viewBibleDetails(bibleId, bibleCode) {
    $.ajax({
        url: `api/manage-bibles.php?action=details&bible_id=${bibleId}&bible_code=${bibleCode}`,
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#booksGrid').html('<div class="loading">Đang tải dữ liệu sách...</div>');
            $('#bibleDetailsModal').css('display', 'block');
        },
        success: function(response) {
            if (response.success) {
                currentBibleDetails = response.data;
                
                // Update Bible info
                $('#modalBibleTitle').text(`Chi tiết Kinh Thánh: ${currentBibleDetails.bible.name}`);
                $('#bibleDetailName').text(currentBibleDetails.bible.name);
                $('#bibleDetailLanguage').text(currentBibleDetails.bible.language_name || currentBibleDetails.bible.language_code || 'N/A');
                $('#bibleDetailStats').text(`${currentBibleDetails.bible.book_count || 0} Sách, ${currentBibleDetails.bible.verse_count || 0} Câu`);
                
                // Render books grid
                renderBooksGrid();
            } else {
                showToast('Lỗi', response.message || 'Không thể tải chi tiết Kinh Thánh.', 'error');
                $('#booksGrid').html('<div class="error">Lỗi tải dữ liệu sách</div>');
            }
        },
        error: function() {
            showToast('Lỗi kết nối', 'Không thể kết nối đến máy chủ.', 'error');
            $('#booksGrid').html('<div class="error">Lỗi kết nối máy chủ</div>');
        }
    });
}

// Render books grid
function renderBooksGrid() {
    if (!currentBibleDetails || !currentBibleDetails.books || currentBibleDetails.books.length === 0) {
        $('#booksGrid').html('<div class="no-data">Không có sách nào cho Kinh Thánh này</div>');
        return;
    }
    
    const bookTypeFilter = $('#bookTypeFilter').val();
    const searchTerm = $('#bookSearch').val().toLowerCase();
    
    let filteredBooks = currentBibleDetails.books;
    
    // Apply search filter
    if (searchTerm) {
        filteredBooks = filteredBooks.filter(book => 
            book.name.toLowerCase().includes(searchTerm) || 
            book.code.toLowerCase().includes(searchTerm)
        );
    }
    
    // Apply availability filter
    if (bookTypeFilter === 'available') {
        filteredBooks = filteredBooks.filter(book => book.is_downloaded);
    } else if (bookTypeFilter === 'missing') {
        filteredBooks = filteredBooks.filter(book => !book.is_downloaded);
    }
    
    if (filteredBooks.length === 0) {
        $('#booksGrid').html('<div class="no-data">Không tìm thấy sách phù hợp với bộ lọc</div>');
        return;
    }
    
    let html = '';
    filteredBooks.forEach(function(book) {
        const bookClass = book.is_downloaded ? 'book-available' : 'book-missing';
        const tooltip = book.is_downloaded 
            ? `${book.name} (${book.verse_count || 0} câu)`
            : `${book.name} (Chưa tải)`;
        
        html += `
            <div class="book-item ${bookClass}" title="${tooltip}" data-code="${book.code}">
                <div class="book-name">${book.name}</div>
                <div class="book-code">${book.code}</div>
                ${book.is_downloaded ? `<div class="book-verses">${book.verse_count || 0} câu</div>` : ''}
            </div>
        `;
    });
    
    $('#booksGrid').html(html);
    
    // Add click event for book details
    $('.book-item').on('click', function() {
        const bookCode = $(this).data('code');
        viewBookDetails(bookCode);
    });
}

// Filter books
function filterBooks() {
    renderBooksGrid();
}

// View book details
function viewBookDetails(bookCode) {
    if (!currentBibleDetails) return;
    
    const book = currentBibleDetails.books.find(b => b.code === bookCode);
    if (!book) return;
    
    if (!book.is_downloaded) {
        if (confirm(`Sách "${book.name}" chưa được tải. Bạn có muốn tải sách này ngay bây giờ?`)) {
            downloadBook(currentBibleDetails.bible.id, currentBibleDetails.bible.code, bookCode);
        }
        return;
    }
    
    // TODO: Implement book details view or redirect to a book view page
    showToast('Thông tin', `Đang xem sách: ${book.name}`, 'info');
}

// Download missing books
function downloadMissingBooks() {
    if (!currentBibleDetails) return;
    
    const missingBooks = currentBibleDetails.books.filter(book => !book.is_downloaded);
    if (missingBooks.length === 0) {
        showToast('Thông tin', 'Không có sách nào cần tải.', 'info');
        return;
    }
    
    if (confirm(`Bạn có muốn tải ${missingBooks.length} sách chưa có dữ liệu không?`)) {
        showToast('Đang xử lý', `Bắt đầu tải ${missingBooks.length} sách...`, 'info');
        
        // Redirect to crawl-verse.php with the Bible and missing books preselected
        const bibleId = currentBibleDetails.bible.id;
        const missingBookCodes = missingBooks.map(book => book.code);
        
        // Store the selection in session storage
        sessionStorage.setItem('preselect_bible_id', bibleId);
        sessionStorage.setItem('preselect_books', JSON.stringify(missingBookCodes));
        
        // Navigate to crawl-verse.php
        window.location.href = 'index.php?page=crawl-verse';
    }
}

// Download a single book
function downloadBook(bibleId, bibleCode, bookCode) {
    // Store the selection in session storage
    sessionStorage.setItem('preselect_bible_id', bibleId);
    sessionStorage.setItem('preselect_books', JSON.stringify([bookCode]));
    
    // Navigate to crawl-verse.php
    window.location.href = 'index.php?page=crawl-verse';
}

// Refresh all Bibles
function refreshAllBibles() {
    if (confirm('Bạn có muốn làm mới dữ liệu thống kê cho tất cả Kinh Thánh không?')) {
        $.ajax({
            url: 'api/manage-bibles.php?action=refresh_all',
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                showToast('Đang xử lý', 'Đang làm mới dữ liệu...', 'info');
                $('#refreshAll').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang làm mới...');
            },
            success: function(response) {
                if (response.success) {
                    showToast('Thành công', response.message || 'Đã làm mới dữ liệu Kinh Thánh.', 'success');
                    loadBiblesData(); // Reload the data
                } else {
                    showToast('Lỗi', response.message || 'Không thể làm mới dữ liệu.', 'error');
                }
            },
            error: function() {
                showToast('Lỗi kết nối', 'Không thể kết nối đến máy chủ.', 'error');
            },
            complete: function() {
                $('#refreshAll').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Làm mới tất cả');
            }
        });
    }
}

// Refresh books for the selected Bible
function refreshSelectedBibleBooks() {
    if (!currentBibleDetails) return;
    
    const bibleId = currentBibleDetails.bible.id;
    const bibleCode = currentBibleDetails.bible.code;
    
    if (confirm(`Bạn có muốn làm mới dữ liệu thống kê cho Kinh Thánh "${currentBibleDetails.bible.name}" không?`)) {
        $.ajax({
            url: `api/manage-bibles.php?action=refresh_bible&bible_id=${bibleId}&bible_code=${bibleCode}`,
            method: 'GET',
            dataType: 'json',
            beforeSend: function() {
                showToast('Đang xử lý', 'Đang làm mới dữ liệu...', 'info');
                $('#refreshAllBooks').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang làm mới...');
            },
            success: function(response) {
                if (response.success) {
                    showToast('Thành công', response.message || 'Đã làm mới dữ liệu Kinh Thánh.', 'success');
                    
                    // Reload current Bible details
                    viewBibleDetails(bibleId, bibleCode);
                    
                    // Reload the main table
                    loadBiblesData();
                } else {
                    showToast('Lỗi', response.message || 'Không thể làm mới dữ liệu.', 'error');
                }
            },
            error: function() {
                showToast('Lỗi kết nối', 'Không thể kết nối đến máy chủ.', 'error');
            },
            complete: function() {
                $('#refreshAllBooks').prop('disabled', false).html('Làm mới tất cả sách');
            }
        });
    }
}

// Export Bible data
function exportBibleData() {
    if (!currentBibleDetails) return;
    
    const bibleId = currentBibleDetails.bible.id;
    const bibleCode = currentBibleDetails.bible.code;
    
    showToast('Đang xử lý', 'Đang chuẩn bị tệp xuất...', 'info');
    
    // Redirect to the export endpoint
    window.location.href = `api/export-bible.php?bible_id=${bibleId}&bible_code=${bibleCode}`;
}

// Export a single Bible
function exportSingleBible(bibleId, bibleCode) {
    showToast('Đang xử lý', 'Đang chuẩn bị tệp xuất...', 'info');
    
    // Redirect to the export endpoint
    window.location.href = `api/export-bible.php?bible_id=${bibleId}&bible_code=${bibleCode}`;
}

// Confirm delete Bible
function confirmDeleteBible() {
    if (!currentBibleDetails) return;
    
    const bibleId = currentBibleDetails.bible.id;
    const bibleCode = currentBibleDetails.bible.code;
    const bibleName = currentBibleDetails.bible.name;
    
    $('#confirmTitle').text('Xác nhận xóa');
    $('#confirmMessage').html(`Bạn có chắc chắn muốn xóa Kinh Thánh <strong>${bibleName}</strong>?<br>Thao tác này sẽ xóa tất cả dữ liệu sách và câu của Kinh Thánh này.`);
    
    confirmationCallback = function() {
        deleteBible(bibleId, bibleCode);
    };
    
    $('#confirmModal').css('display', 'block');
}

// Confirm delete single Bible from the main table
function confirmDeleteSingleBible(bibleId, bibleCode) {
    console.log('Deleting Bible:', bibleId, bibleCode);
    console.log('All Bibles Data:', allBiblesData);
    
    // Convert IDs to strings for comparison
    const strBibleId = String(bibleId);
    const bible = allBiblesData.find(b => String(b.id) === strBibleId);
    
    if (!bible) {
        console.error('Bible not found:', bibleId);
        showToast('Lỗi', 'Không tìm thấy thông tin Kinh Thánh.', 'error');
        return;
    }
    
    $('#confirmTitle').text('Xác nhận xóa');
    $('#confirmMessage').html(`Bạn có chắc chắn muốn xóa Kinh Thánh <strong>${bible.name}</strong>?<br>Thao tác này sẽ xóa tất cả dữ liệu sách và câu của Kinh Thánh này.`);
    
    confirmationCallback = function() {
        deleteBible(bibleId, bibleCode);
    };
    
    $('#confirmModal').css('display', 'block');
}

// Delete Bible
function deleteBible(bibleId, bibleCode) {
    $.ajax({
        url: `api/manage-bibles.php?action=delete&bible_id=${bibleId}&bible_code=${bibleCode}`,
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            showToast('Đang xử lý', 'Đang xóa dữ liệu...', 'info');
            // Disable delete buttons to prevent multiple clicks
            $('.btn-delete').prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                showToast('Thành công', response.message || 'Đã xóa dữ liệu Kinh Thánh.', 'success');
                
                // Close the modal if open
                closeModals();
                
                // Reload the main table
                loadBiblesData();
            } else {
                console.error('Delete error:', response);
                let errorMsg = response.message || 'Không thể xóa dữ liệu.';
                
                // If we have log data, show it in the console
                if (response.data && response.data.log) {
                    console.error('Delete log:', response.data.log);
                    errorMsg += ' Xem chi tiết trong bảng điều khiển (F12).';
                }
                
                showToast('Lỗi', errorMsg, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', status, error);
            showToast('Lỗi kết nối', 'Không thể kết nối đến máy chủ: ' + error, 'error');
        },
        complete: function() {
            // Re-enable delete buttons
            $('.btn-delete').prop('disabled', false);
        }
    });
}

// Handle confirmation action
function handleConfirmAction() {
    if (typeof confirmationCallback === 'function') {
        confirmationCallback();
    }
    closeModals();
}

// Close all modals
function closeModals() {
    $('.modal').css('display', 'none');
    confirmationCallback = null;
}

// Format file size
function formatSize(bytes) {
    if (bytes === 0) return '0 B';
    
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    
    return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + sizes[i];
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
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