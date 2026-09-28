document.addEventListener('DOMContentLoaded', () => {
    
    // Generate Strong Password Helper
    function generateStrongPassword() {
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const numbers = '0123456789';
        const specials = '!@#$%^&*()_+~`|}{[]:;?><,./-=';
        const all = uppercase + lowercase + numbers + specials;
        
        let pass = '';
        pass += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
        pass += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
        pass += numbers.charAt(Math.floor(Math.random() * numbers.length));
        pass += specials.charAt(Math.floor(Math.random() * specials.length));
        
        for (let i = 0; i < 6; i++) {
            pass += all.charAt(Math.floor(Math.random() * all.length));
        }
        
        // Shuffle
        return pass.split('').sort(function(){return 0.5-Math.random()}).join('');
    }

    // Modal Thêm/Sửa
    const userModal = document.getElementById('userModal');
    const modalTitle = document.getElementById('userModalTitle');
    const passwordGroup = document.getElementById('passwordGroup');

    document.getElementById('btnAddNewUser')?.addEventListener('click', () => {
        document.getElementById('userId').value = '';
        document.getElementById('userName').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userPhone').value = '';
        document.getElementById('userRole').value = '';
        document.getElementById('userPassword').value = '';
        
        modalTitle.textContent = 'Thêm Nhân viên Mới';
        
        // Remove Admin option if it exists for adding new
        const roleSelect = document.getElementById('userRole');
        Array.from(roleSelect.options).forEach(opt => {
            if (opt.value == '1') {
                opt.disabled = true;
                opt.style.display = 'none';
            }
        });
        roleSelect.disabled = false;

        passwordGroup.style.display = 'block'; // Hiển thị khung nhập pass
        userModal.classList.add('active');
    });

    document.querySelectorAll('.btn-edit-user').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('userId').value = this.getAttribute('data-id');
            document.getElementById('userName').value = this.getAttribute('data-name');
            document.getElementById('userEmail').value = this.getAttribute('data-email');
            document.getElementById('userPhone').value = this.getAttribute('data-phone');
            
            const roleId = this.getAttribute('data-role');
            const roleSelect = document.getElementById('userRole');
            
            // Nếu là Admin gốc, khóa sửa chức vụ
            if (roleId == '1') {
                Array.from(roleSelect.options).forEach(opt => {
                    opt.disabled = false;
                    opt.style.display = 'block';
                });
                roleSelect.value = roleId;
                roleSelect.disabled = true; // Không cho đổi quyền của Admin
            } else {
                Array.from(roleSelect.options).forEach(opt => {
                    if (opt.value == '1') {
                        opt.disabled = true;
                        opt.style.display = 'none';
                    } else {
                        opt.disabled = false;
                        opt.style.display = 'block';
                    }
                });
                roleSelect.value = roleId;
                roleSelect.disabled = false;
            }
            
            modalTitle.textContent = 'Sửa Thông Tin Nhân viên';
            passwordGroup.style.display = 'none'; // Sửa thì không nhập pass ở đây
            userModal.classList.add('active');
        });
    });

    document.getElementById('btnCloseUserModal')?.addEventListener('click', () => {
        userModal.classList.remove('active');
    });

    document.getElementById('btnGenInitialPass')?.addEventListener('click', () => {
        document.getElementById('userPassword').value = generateStrongPassword();
    });

    document.getElementById('btnSaveUser')?.addEventListener('click', async () => {
        const id = document.getElementById('userId').value;
        const payload = {
            id: id,
            full_name: document.getElementById('userName').value,
            email: document.getElementById('userEmail').value,
            phone: document.getElementById('userPhone').value,
            role_id: document.getElementById('userRole').value,
        };

        // Nếu disabled, value sẽ trống, ta lấy từ option đang chọn
        if (document.getElementById('userRole').disabled) {
            payload.role_id = '1';
        }

        let endpoint = '/fashion-shop/api/admin/users/update.php';
        
        if (!id) {
            endpoint = '/fashion-shop/api/admin/users/create.php';
            payload.password = document.getElementById('userPassword').value;
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

    // Modal Cấp lại mật khẩu
    const resetModal = document.getElementById('resetPasswordModal');
    document.querySelectorAll('.btn-reset-password').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('resetPasswordUserId').value = this.getAttribute('data-id');
            document.getElementById('resetPasswordUserName').textContent = this.getAttribute('data-name');
            document.getElementById('newPassword').value = '';
            resetModal.classList.add('active');
        });
    });

    document.getElementById('btnCloseResetPasswordModal')?.addEventListener('click', () => {
        resetModal.classList.remove('active');
    });

    document.getElementById('btnGeneratePassword')?.addEventListener('click', () => {
        document.getElementById('newPassword').value = generateStrongPassword();
    });

    document.getElementById('btnSaveNewPassword')?.addEventListener('click', async () => {
        const payload = {
            id: document.getElementById('resetPasswordUserId').value,
            password: document.getElementById('newPassword').value
        };

        if (!payload.password) {
            alert('Vui lòng nhập hoặc tạo mật khẩu mới!');
            return;
        }

        try {
            const response = await fetch('/fashion-shop/api/admin/users/reset_password.php', {
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

    // Toggle Status
    document.querySelectorAll('.btn-toggle-status').forEach(btn => {
        btn.addEventListener('click', async function() {
            const currentStatus = this.getAttribute('data-status');
            const id = this.getAttribute('data-id');
            const actionText = currentStatus === 'active' ? 'KHÓA' : 'MỞ KHÓA';
            
            if (confirm(`Bạn có chắc chắn muốn ${actionText} tài khoản nhân viên này?`)) {
                try {
                    const response = await fetch('/fashion-shop/api/admin/users/toggle_status.php', {
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

    // Delete User
    document.querySelectorAll('.btn-delete-user').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            
            if (confirm('Bạn có chắc chắn muốn XÓA VĨNH VIỄN nhân viên này? Thao tác này không thể hoàn tác!')) {
                try {
                    const response = await fetch('/fashion-shop/api/admin/users/delete.php', {
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
