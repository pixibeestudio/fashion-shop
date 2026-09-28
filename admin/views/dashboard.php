<div class="page-header">
    <div class="page-title">Tổng quan hệ thống (Dashboard)</div>
    <div class="header-actions">
        <select id="timeFilter" class="form-control" style="width: 200px; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
            <option value="today">Hôm nay</option>
            <option value="week">Tuần này</option>
            <option value="month" selected>Tháng này</option>
            <option value="year">Năm nay</option>
            <option value="all">Tất cả thời gian</option>
        </select>
    </div>
</div>

<!-- Skeleton Loading Overlay -->
<div id="dashboardLoader" class="skeleton-overlay" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); z-index: 10; justify-content: center; align-items: center;">
    <div class="spinner"><i class="fas fa-circle-notch fa-spin fa-3x" style="color: var(--primary);"></i></div>
</div>

<div style="position: relative; min-height: 500px;" id="dashboardContent">
    <!-- KPI Cards -->
    <div class="kpi-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;">
        <div class="kpi-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px;">
            <div class="kpi-icon" style="background: #e0e7ff; color: #4f46e5; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="fas fa-wallet"></i>
            </div>
            <div>
                <div style="color: #64748b; font-size: 14px;">Tổng Doanh thu</div>
                <div class="kpi-value" id="kpiRevenue" style="font-size: 24px; font-weight: 700; color: #1e293b;">0 đ</div>
            </div>
        </div>
        
        <div class="kpi-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px;">
            <div class="kpi-icon" style="background: #dcfce7; color: #16a34a; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div>
                <div style="color: #64748b; font-size: 14px;">Tổng Đơn hàng</div>
                <div class="kpi-value" id="kpiOrders" style="font-size: 24px; font-weight: 700; color: #1e293b;">0</div>
            </div>
        </div>
        
        <div class="kpi-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px;">
            <div class="kpi-icon" style="background: #fef9c3; color: #ca8a04; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <div style="color: #64748b; font-size: 14px;">Đơn Cần Xử Lý</div>
                <div class="kpi-value" id="kpiPending" style="font-size: 24px; font-weight: 700; color: #1e293b;">0</div>
            </div>
        </div>

        <div class="kpi-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px;">
            <div class="kpi-icon" style="background: #ffedd5; color: #ea580c; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div style="color: #64748b; font-size: 14px;">Khách hàng mới</div>
                <div class="kpi-value" id="kpiCustomers" style="font-size: 24px; font-weight: 700; color: #1e293b;">0</div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-container" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h3 style="margin-bottom: 20px; font-size: 16px; color: #334155;">Biểu đồ Doanh thu</h3>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Split View: Top Products & Recent Orders -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <!-- Top Products -->
        <div class="table-container" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #334155;">Top 5 Sản phẩm Bán chạy</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; text-align: left; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 10px 0;">Sản phẩm</th>
                        <th style="padding: 10px 0; text-align: right;">Đã bán</th>
                        <th style="padding: 10px 0; text-align: right;">Doanh thu</th>
                    </tr>
                </thead>
                <tbody id="topProductsBody">
                    <!-- JS Render -->
                </tbody>
            </table>
        </div>

        <!-- Recent Orders -->
        <div class="table-container" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="margin-bottom: 15px; font-size: 16px; color: #334155;">5 Đơn hàng Gần nhất</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid #e2e8f0; color: #64748b; text-align: left; font-size: 12px; text-transform: uppercase;">
                        <th style="padding: 10px 0;">Mã ĐH</th>
                        <th style="padding: 10px 0;">Khách hàng</th>
                        <th style="padding: 10px 0;">Trạng thái</th>
                        <th style="padding: 10px 0; text-align: right;">Tổng tiền</th>
                    </tr>
                </thead>
                <tbody id="recentOrdersBody">
                    <!-- JS Render -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/fashion-shop/assets/js/dashboard.js"></script>
