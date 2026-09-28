document.addEventListener('DOMContentLoaded', function() {
    const promoModal = document.getElementById('promoModal');
    const modalTitle = document.getElementById('modalTitle');
    
    // Open Add Modal
    document.getElementById('btnShowAddModal')?.addEventListener('click', () => {
        document.getElementById('promoId').value = '';
        document.getElementById('promoCode').value = '';
        document.getElementById('promoType').value = 'percent';
        document.getElementById('promoValue').value = '';
        
        // Default start date to now, end date to tomorrow
        const now = new Date();
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        // Format to YYYY-MM-DDThh:mm
        const formatForDatetimeLocal = (d) => {
            return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        };

        document.getElementById('promoStart').value = formatForDatetimeLocal(now);
        document.getElementById('promoEnd').value = formatForDatetimeLocal(tomorrow);

        modalTitle.textContent = 'Thêm Khuyến Mãi Mới';
        promoModal.classList.add('active');
    });

    // Close Modal
    document.getElementById('btnClosePromoModal')?.addEventListener('click', () => {
        promoModal.classList.remove('active');
    });

    // Edit Promo
    document.querySelectorAll('.btn-edit-promo').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('promoId').value = this.getAttribute('data-id');
            document.getElementById('promoCode').value = this.getAttribute('data-code');
            document.getElementById('promoType').value = this.getAttribute('data-type');
            document.getElementById('promoValue').value = this.getAttribute('data-value');
            document.getElementById('promoStart').value = this.getAttribute('data-start');
            document.getElementById('promoEnd').value = this.getAttribute('data-end');
            
            modalTitle.textContent = 'Sửa Khuyến Mãi';
            promoModal.classList.add('active');
        });
    });

    // Generate Random Code
    document.getElementById('btnGenCode')?.addEventListener('click', () => {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < 8; i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('promoCode').value = code;
    });

    // Save Promo
    document.getElementById('btnSavePromo')?.addEventListener('click', async () => {
        const id = document.getElementById('promoId').value;
        const payload = {
            id: id,
            code: document.getElementById('promoCode').value.trim(),
            discount_type: document.getElementById('promoType').value,
            discount_value: document.getElementById('promoValue').value,
            start_date: document.getElementById('promoStart').value,
            end_date: document.getElementById('promoEnd').value
        };

        let endpoint = '/fashion-shop/api/admin/promotions/update.php';
        if (!id) {
            endpoint = '/fashion-shop/api/admin/promotions/create.php';
        }

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert('Lỗi: ' + result.message);
            }
        } catch (error) {
            alert('Lỗi kết nối máy chủ!');
        }
    });

    // Toggle Status
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            
            const actionText = currentStatus === 'disabled' ? 'mở khóa' : 'vô hiệu hóa';
            
            if (confirm(`Bạn có chắc chắn muốn ${actionText} mã khuyến mãi này?`)) {
                try {
                    const response = await fetch('/fashion-shop/api/admin/promotions/toggle_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const result = await response.json();
                    
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert('Lỗi: ' + result.message);
                    }
                } catch (error) {
                    alert('Lỗi kết nối máy chủ!');
                }
            }
        });
    });
});
