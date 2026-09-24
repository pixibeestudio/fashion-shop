document.addEventListener('DOMContentLoaded', () => {
    const btnOpen = document.getElementById('btnOpenCategoryModal');
    const modal = document.getElementById('categoryModal');
    const btnClose = document.getElementById('btnCloseCategoryModal');
    const categoryForm = document.getElementById('categoryForm');
    
    const catIdInput = document.getElementById('catId');
    const catNameInput = document.getElementById('catName');
    const catSlugInput = document.getElementById('catSlug');
    const catParentInput = document.getElementById('catParent');
    const catDescInput = document.getElementById('catDesc');
    const charCount = document.getElementById('charCount');
    
    const modalTitle = document.getElementById('modalTitle');
    const btnSubmitForm = document.getElementById('btnSubmitForm');

    // --- Reset Form Helper ---
    const resetForm = () => {
        if (categoryForm) categoryForm.reset();
        if (catIdInput) catIdInput.value = '';
        if (modalTitle) modalTitle.textContent = 'Thêm Danh Mục Mới';
        if (btnSubmitForm) btnSubmitForm.textContent = 'Thêm Danh Mục';
        if (charCount) charCount.textContent = '0/500';
    };

    // --- Open / Close Modal ---
    if (btnOpen && modal) {
        btnOpen.addEventListener('click', () => {
            resetForm(); // Xóa dữ liệu cũ khi bấm thêm mới
            modal.classList.add('active');
        });
    }

    if (btnClose && modal) {
        btnClose.addEventListener('click', () => {
            modal.classList.remove('active');
            resetForm(); // Xóa dữ liệu cũ khi Hủy
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
            resetForm(); // Xóa dữ liệu cũ khi bấm ra ngoài
        }
    });

    // --- Textarea Character Counter ---
    if (catDescInput && charCount) {
        catDescInput.addEventListener('input', function() {
            charCount.textContent = this.value.length + '/500';
        });
    }

    // --- Auto Slug ---
    if (catNameInput && catSlugInput) {
        catNameInput.addEventListener('input', function() {
            let title = this.value.toLowerCase();
            title = title.replace(/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/g, 'a');
            title = title.replace(/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/g, 'e');
            title = title.replace(/(ì|í|ị|ỉ|ĩ)/g, 'i');
            title = title.replace(/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/g, 'o');
            title = title.replace(/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/g, 'u');
            title = title.replace(/(ỳ|ý|ỵ|ỷ|ỹ)/g, 'y');
            title = title.replace(/(đ)/g, 'd');
            title = title.replace(/([^a-z0-9-\s])/g, '');
            title = title.replace(/(\s+)/g, '-');
            title = title.replace(/-+/g, '-');
            title = title.replace(/^-+|-+$/g, '');
            catSlugInput.value = title;
        });
    }

    // --- Submit Form (Create / Update) ---
    if (categoryForm) {
        categoryForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(categoryForm);
            
            // Nếu có ID thì là Update, không thì là Create
            const id = catIdInput.value;
            const endpoint = id ? '/fashion-shop/api/admin/categories/update.php' : '/fashion-shop/api/admin/categories/create.php';
            
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert("Lỗi: " + result.message);
                }
            } catch (error) {
                alert("Đã xảy ra lỗi kết nối với máy chủ.");
                console.error(error);
            }
        });
    }

    // --- Edit & Delete Event Delegation ---
    document.addEventListener('click', async (e) => {
        // Edit Button Click
        const btnEdit = e.target.closest('.btn-edit');
        if (btnEdit) {
            const id = btnEdit.dataset.id;
            try {
                const res = await fetch(`/fashion-shop/api/admin/categories/get.php?id=${id}`);
                const data = await res.json();
                if (data.success && data.category) {
                    const cat = data.category;
                    catIdInput.value = cat.id;
                    catNameInput.value = cat.name;
                    catSlugInput.value = cat.slug;
                    catParentInput.value = cat.parent_id || '';
                    catDescInput.value = cat.description || '';
                    charCount.textContent = (cat.description ? cat.description.length : 0) + '/500';
                    
                    modalTitle.textContent = 'Sửa Danh Mục';
                    btnSubmitForm.textContent = 'Lưu Thay Đổi';
                    modal.classList.add('active');
                } else {
                    alert("Không tìm thấy dữ liệu danh mục.");
                }
            } catch (error) {
                alert("Lỗi kết nối khi tải dữ liệu.");
            }
        }

        // Delete Button Click
        const btnDelete = e.target.closest('.btn-delete');
        if (btnDelete) {
            const id = btnDelete.dataset.id;
            if (confirm("Bạn có chắc chắn muốn xóa danh mục này? Hệ thống sẽ kiểm tra xem danh mục này có chứa sản phẩm hoặc danh mục con hay không.")) {
                try {
                    const res = await fetch(`/fashion-shop/api/admin/categories/delete.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${id}`
                    });
                    const result = await res.json();
                    
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert("Không thể xóa: " + result.message);
                    }
                } catch (error) {
                    alert("Lỗi kết nối máy chủ.");
                }
            }
        }
    });
});
