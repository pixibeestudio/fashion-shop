document.addEventListener('DOMContentLoaded', function() {
    const timeFilter = document.getElementById('timeFilter');
    const loader = document.getElementById('dashboardLoader');
    let revenueChartInstance = null;

    // Format currency
    const formatCurrency = (val) => {
        return new Intl.NumberFormat('vi-VN').format(val) + ' đ';
    };

    // Format status
    const formatStatus = (status) => {
        const map = {
            'pending': '<span class="tag" style="background:#fef9c3; color:#854d0e;">Chờ xử lý</span>',
            'processing': '<span class="tag" style="background:#e0e7ff; color:#3730a3;">Đang chuẩn bị</span>',
            'shipped': '<span class="tag" style="background:#f3e8ff; color:#6b21a8;">Đang giao</span>',
            'delivered': '<span class="tag" style="background:#dcfce7; color:#166534;">Đã giao</span>',
            'cancelled': '<span class="tag" style="background:#fef2f2; color:#991b1b;">Đã hủy</span>'
        };
        return map[status] || status;
    };

    // Animation Đếm số
    const animateValue = (obj, start, end, duration, isCurrency = false) => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const currentVal = Math.floor(progress * (end - start) + start);
            obj.innerHTML = isCurrency ? formatCurrency(currentVal) : currentVal;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                obj.innerHTML = isCurrency ? formatCurrency(end) : end;
            }
        };
        window.requestAnimationFrame(step);
    };

    const loadDashboardData = async () => {
        loader.style.display = 'flex';
        const filter = timeFilter.value;
        
        try {
            const response = await fetch(`/fashion-shop/api/admin/dashboard/get_data.php?filter=${filter}`);
            const result = await response.json();
            
            if (result.success) {
                const data = result.data;

                // Cập nhật KPIs với hiệu ứng đếm số
                const kpiRev = document.getElementById('kpiRevenue');
                const kpiOrd = document.getElementById('kpiOrders');
                const kpiPend = document.getElementById('kpiPending');
                const kpiCust = document.getElementById('kpiCustomers');
                
                // Lưu lại giá trị hiện tại để đếm tiếp (giả sử nếu đã có)
                animateValue(kpiRev, 0, data.kpis.revenue, 1000, true);
                animateValue(kpiOrd, 0, data.kpis.total_orders, 1000);
                animateValue(kpiPend, 0, data.kpis.pending_orders, 1000);
                animateValue(kpiCust, 0, data.kpis.new_customers, 1000);

                // Cập nhật Chart
                updateChart(data.chart.labels, data.chart.data);

                // Cập nhật Top Products
                const topProductsBody = document.getElementById('topProductsBody');
                topProductsBody.innerHTML = '';
                if (data.top_products.length === 0) {
                    topProductsBody.innerHTML = '<tr><td colspan="3" style="text-align:center; padding:15px; color:#64748b;">Chưa có dữ liệu</td></tr>';
                } else {
                    data.top_products.forEach(p => {
                        topProductsBody.innerHTML += `
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 0;"><strong>${p.product_name}</strong></td>
                                <td style="padding: 12px 0; text-align: right; color:#3b82f6; font-weight:bold;">${p.total_sold}</td>
                                <td style="padding: 12px 0; text-align: right; color:#ef4444; font-weight:bold;">${formatCurrency(p.total_revenue)}</td>
                            </tr>
                        `;
                    });
                }

                // Cập nhật Recent Orders
                const recentOrdersBody = document.getElementById('recentOrdersBody');
                recentOrdersBody.innerHTML = '';
                if (data.recent_orders.length === 0) {
                    recentOrdersBody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:15px; color:#64748b;">Chưa có dữ liệu</td></tr>';
                } else {
                    data.recent_orders.forEach(o => {
                        recentOrdersBody.innerHTML += `
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 0;"><a href="?page=order_detail&id=${o.id}" style="color:var(--primary); font-weight:bold; text-decoration:none;">#${o.order_number}</a></td>
                                <td style="padding: 12px 0;">${o.customer_name}</td>
                                <td style="padding: 12px 0;">${formatStatus(o.shipping_status)}</td>
                                <td style="padding: 12px 0; text-align: right; color:#ef4444; font-weight:bold;">${formatCurrency(o.total)}</td>
                            </tr>
                        `;
                    });
                }

            }
        } catch (e) {
            console.error('Lỗi tải dữ liệu Dashboard:', e);
        } finally {
            // Hiệu ứng mờ dần skeleton
            setTimeout(() => {
                loader.style.opacity = 0;
                setTimeout(() => {
                    loader.style.display = 'none';
                    loader.style.opacity = 1;
                }, 300);
            }, 300);
        }
    };

    const updateChart = (labels, data) => {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        
        if (revenueChartInstance) {
            revenueChartInstance.destroy();
        }

        // Tạo gradient background
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.8)'); // --primary color
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.2)');

        revenueChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: data,
                    backgroundColor: gradient,
                    borderRadius: 6, // Bo góc cột
                    borderSkipped: false,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += formatCurrency(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false,
                        },
                        ticks: {
                            callback: function(value, index, values) {
                                return value >= 1000000 ? (value / 1000000) + 'tr' : formatCurrency(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false,
                        }
                    }
                }
            }
        });
    };

    // Listen filter change
    timeFilter.addEventListener('change', loadDashboardData);

    // Initial load
    loadDashboardData();
});
