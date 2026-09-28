document.addEventListener('DOMContentLoaded', () => {
    // Modal View Customer
    const viewModal = document.getElementById('viewCustomerModal');
    
    document.querySelectorAll('.btn-view-customer').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            try {
                const response = await fetch(`/fashion-shop/api/admin/customers/get.php?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const c = result.data;
                    
                    document.getElementById('vcName').textContent = c.full_name;
                    document.getElementById('vcAvatar').textContent = c.full_name.charAt(0).toUpperCase();
                    document.getElementById('vcEmail').textContent = c.email;
                    document.getElementById('vcPhone').textContent = c.phone || 'Chưa cung cấp';
                    document.getElementById('vcDate').textContent = c.created_at;
                    
                    const statusEl = document.getElementById('vcStatus');
                    if (c.status === 'active') {
                        statusEl.textContent = 'Đang hoạt động';
                        statusEl.style.color = '#166534';
                    } else {
                        statusEl.textContent = 'Bị Khóa';
                        statusEl.style.color = '#991b1b';
                    }

                    // Render addresses
                    const addressContainer = document.getElementById('vcAddresses');
                    addressContainer.innerHTML = '';

                    if (c.addresses && c.addresses.length > 0) {
                        c.addresses.forEach(addr => {
                            const badge = addr.is_default == 1 ? '<span class="tag" style="background:#dbeafe; color:#1e40af; font-size:11px; padding:2px 6px;">Mặc định</span>' : '';
                            addressContainer.innerHTML += `
                                <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin-bottom: 10px; background: #f8fafc;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                        <strong>${addr.receiver_name} - ${addr.phone}</strong>
                                        ${badge}
                                    </div>
                                    <div style="color: #475569; font-size: 14px;">
                                        ${addr.address_line}<br>
                                        ${addr.ward}, ${addr.district}, ${addr.city}
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        addressContainer.innerHTML = '<p style="color: #64748b; font-style: italic;">Khách hàng chưa thêm địa chỉ nào.</p>';
                    }

                    viewModal.classList.add('active');

                    // Setup data for Edit & Reset Password
                    document.getElementById('editCustomerId').value = c.id;
                    document.getElementById('editCustomerName').value = c.full_name;
                    document.getElementById('editCustomerEmail').value = c.email;
                    document.getElementById('editCustomerPhone').value = c.phone || '';

                    document.getElementById('resetPasswordCustomerId').value = c.id;
                    document.getElementById('resetPasswordCustomerName').textContent = c.full_name;

                } else {
                    alert('Lỗi: ' + result.message);
                }
            } catch (error) {
                alert('Lỗi kết nối máy chủ!');
            }
        });
    });

    document.getElementById('btnCloseCustomerModal')?.addEventListener('click', () => {
        viewModal.classList.remove('active');
    });

    // Toggle Status
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        btn.addEventListener('click', async function() {
            const currentStatus = this.getAttribute('data-status');
            const id = this.getAttribute('data-id');
            const actionText = currentStatus === 'active' ? 'KHÓA' : 'MỞ KHÓA';
            
            if (confirm(`Bạn có chắc chắn muốn ${actionText} tài khoản khách hàng này?`)) {
                try {
                    const response = await fetch('/fashion-shop/api/admin/customers/toggle_status.php', {
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

    // Chỉnh sửa thông tin
    const editModal = document.getElementById('editCustomerModal');
    document.getElementById('btnEditCustomerInfo')?.addEventListener('click', () => {
        viewModal.classList.remove('active');
        editModal.classList.add('active');
    });

    document.getElementById('btnCloseEditCustomerModal')?.addEventListener('click', () => {
        editModal.classList.remove('active');
    });

    document.getElementById('btnSaveCustomerInfo')?.addEventListener('click', async () => {
        const payload = {
            id: document.getElementById('editCustomerId').value,
            full_name: document.getElementById('editCustomerName').value,
            email: document.getElementById('editCustomerEmail').value,
            phone: document.getElementById('editCustomerPhone').value
        };

        try {
            const response = await fetch('/fashion-shop/api/admin/customers/update.php', {
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

    // Cấp lại mật khẩu
    const resetModal = document.getElementById('resetPasswordModal');
    document.getElementById('btnResetPassword')?.addEventListener('click', () => {
        viewModal.classList.remove('active');
        document.getElementById('newCustomerPassword').value = '';
        resetModal.classList.add('active');
    });

    document.getElementById('btnCloseResetPasswordModal')?.addEventListener('click', () => {
        resetModal.classList.remove('active');
    });

    document.getElementById('btnGeneratePassword')?.addEventListener('click', () => {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        let pass = '';
        for (let i = 0; i < 8; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
        document.getElementById('newCustomerPassword').value = pass;
    });

    document.getElementById('btnSaveNewPassword')?.addEventListener('click', async () => {
        const payload = {
            id: document.getElementById('resetPasswordCustomerId').value,
            password: document.getElementById('newCustomerPassword').value
        };

        if (!payload.password) {
            alert('Vui lòng nhập hoặc tạo mật khẩu mới!');
            return;
        }

        try {
            const response = await fetch('/fashion-shop/api/admin/customers/reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                resetModal.classList.remove('active');
            } else {
                alert('Lỗi: ' + result.message);
            }
        } catch (error) {
            alert('Lỗi kết nối máy chủ!');
        }
    });
});
