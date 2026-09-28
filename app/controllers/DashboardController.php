<?php
require_once __DIR__ . '/../models/Dashboard.php';
require_once __DIR__ . '/../helpers/session.php';

class DashboardController {
    private $dashboardModel;

    public function __construct() {
        $this->dashboardModel = new Dashboard();
    }

    public function getData() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $filter = $_GET['filter'] ?? 'month';
            
            // Tính toán start_date và end_date
            $now = new DateTime();
            $endDate = $now->format('Y-m-d 23:59:59');
            $startDate = '';

            switch ($filter) {
                case 'today':
                    $startDate = $now->format('Y-m-d 00:00:00');
                    break;
                case 'week':
                    // Đầu tuần (Thứ 2)
                    $weekStart = clone $now;
                    if ($weekStart->format('N') != 1) {
                        $weekStart->modify('last monday');
                    }
                    $startDate = $weekStart->format('Y-m-d 00:00:00');
                    break;
                case 'month':
                    $startDate = $now->format('Y-m-01 00:00:00');
                    break;
                case 'year':
                    $startDate = $now->format('Y-01-01 00:00:00');
                    break;
                case 'all':
                    $startDate = '2000-01-01 00:00:00';
                    break;
                default:
                    $startDate = $now->format('Y-m-01 00:00:00'); // default month
                    break;
            }

            // Mock Data Generator logic could be external, here we just fetch from DB
            $kpis = $this->dashboardModel->getKpis($startDate, $endDate);
            $chartDataRaw = $this->dashboardModel->getChartData($startDate, $endDate, $filter);
            $topProducts = $this->dashboardModel->getTopProducts($startDate, $endDate, 5);
            $recentOrders = $this->dashboardModel->getRecentOrders(5);

            // Format Chart Data for Chart.js
            $labels = [];
            $data = [];
            
            // Xử lý làm mịn biểu đồ (điền 0 cho những ngày/tháng không có dữ liệu)
            if ($filter == 'today') {
                $labels = [$now->format('d/m/Y')];
                $data = [isset($chartDataRaw[0]) ? (float)$chartDataRaw[0]['revenue'] : 0];
            } else if ($filter == 'week' || $filter == 'month') {
                $periodStart = new DateTime($startDate);
                $periodEnd = new DateTime($endDate);
                $interval = new DateInterval('P1D');
                $daterange = new DatePeriod($periodStart, $interval, $periodEnd->modify('+1 day'));
                
                $dataMap = [];
                foreach ($chartDataRaw as $row) {
                    $dataMap[$row['label']] = (float)$row['revenue'];
                }

                foreach ($daterange as $date) {
                    $d = $date->format('Y-m-d');
                    $labels[] = $date->format('d/m');
                    $data[] = isset($dataMap[$d]) ? $dataMap[$d] : 0;
                }
            } else if ($filter == 'year' || $filter == 'all') {
                $dataMap = [];
                foreach ($chartDataRaw as $row) {
                    $dataMap[$row['label']] = (float)$row['revenue'];
                }

                if ($filter == 'year') {
                    for ($i = 1; $i <= 12; $i++) {
                        $m = str_pad($i, 2, '0', STR_PAD_LEFT);
                        $ym = $now->format('Y') . '-' . $m;
                        $labels[] = "Tháng $i";
                        $data[] = isset($dataMap[$ym]) ? $dataMap[$ym] : 0;
                    }
                } else {
                    foreach ($chartDataRaw as $row) {
                        $labels[] = $row['label'];
                        $data[] = (float)$row['revenue'];
                    }
                }
            }

            $chart = [
                'labels' => $labels,
                'data' => $data
            ];

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => [
                    'kpis' => $kpis,
                    'chart' => $chart,
                    'top_products' => $topProducts,
                    'recent_orders' => $recentOrders
                ]
            ]);
            exit;
        }
    }
}
