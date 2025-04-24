<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmOrderValidator;
use App\Http\Requests\UpdateOrderValidator;
use App\Http\Requests\UpdateStatusOrderValidator;
use App\ModelQuery\CartModel;
use App\ModelQuery\OrderModel;
use App\Models\Order;
use App\Models\User;
use App\Transformers\OrderTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $model;
    protected $cartModel;

    public function __construct(OrderModel $model, CartModel $cartModel)
    {
        $this->model = $model;
        $this->cartModel = $cartModel;
    }

    public function getAllOrders(Request $request)
    {
        $orders = $this->model->getAllOrders($request);
        return fractal($orders, new OrderTransformer())->respond();
    }

    public function getOrderDetail(Request $request, $code)
    {
        $request['limit'] = 1;
        $request['code'] = $code;
        $orders = $this->model->getAllOrders($request);
        return fractal($orders, new OrderTransformer())->respond();
    }

    public function confirmOrder(ConfirmOrderValidator $request)
    {
        $cart = $this->cartModel->getCart($request);
        return $this->model->createOrder($request, $cart);
    }

    public function getMyOrders(Request $request)
    {
        $orders = $this->model->getMyOrders($request);
        // return $orders;

        return fractal($orders, new OrderTransformer())->respond();
    }

    public function getMyOrder(Request $request, $code)
    {
        $request['limit'] = 1;
        $request['code'] = $code;
        $orders = $this->model->getMyOrders($request);
        // return $orders;

        return fractal($orders, new OrderTransformer())->respond();
    }

    public function getStatisticRevenueByWeek(Request $request)
    {
        $thuHai = Carbon::now()->startOfWeek();
        $chuNhat = Carbon::now()->endOfWeek();
        $data = Order::whereNull('deleted_at')->where('created_at', '>=', $thuHai)->where('created_at', '<=', $chuNhat)->sum('total_price');
        return response()->json(['data' => ['total_price' => $data, 'total_price_text' => number_format($data, 0, ',', '.') . 'đ']], 200);
    }

    public function getStatisticRevenueByMonth(Request $request)
    {
        $year = Carbon::now()->year;

        // Lấy doanh thu theo tháng có trong DB
        $results = DB::table('orders')
            ->selectRaw('MONTH(created_at) as month, SUM(total_price) as revenue')
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('revenue', 'month');  // trả về dạng [month => revenue]

        // Tạo mảng 12 tháng, gán doanh thu nếu có, không thì = 0
        $monthlyRevenue = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyRevenue[$month] = number_format($results[$month] ?? 0, 0, ',', '.') . 'đ';
        }
        // (Tuỳ chọn) trả về dạng JSON cho biểu đồ
        return response()->json($monthlyRevenue);
    }

    public function getStatisticNewOrdersByWeek(Request $request)
    {
        $thuHai = Carbon::now()->startOfWeek();
        $chuNhat = Carbon::now()->endOfWeek();
        $data = Order::whereNull('deleted_at')->where('created_at', '>=', $thuHai)->where('created_at', '<=', $chuNhat)->count();
        return response()->json(['data' => ['total' => $data]], 200);
    }

    public function getStatisticNewOrdersByMonth(Request $request)
    {
        $year = Carbon::now()->year;
        // Lấy doanh thu theo tháng có trong DB
        $results = DB::table(DB::raw('(SELECT 1 AS month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) AS months'))
            ->leftJoin('orders', DB::raw('MONTH(orders.created_at)'), '=', DB::raw('months.month'))
            ->whereYear('orders.created_at', $year)
            ->select(DB::raw('months.month'), DB::raw('IFNULL(COUNT(orders.id), 0) AS total_orders'))
            ->groupBy('months.month')
            ->orderBy('months.month')
            ->get();

        // Tạo mảng 12 tháng, gán doanh thu nếu có, không thì = 0
        $monthlyOrders = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyOrders[$month] = $results[$month] ?? 0;
            foreach ($results as $result) {
                if ($result->month == $month) {
                    $monthlyOrders[$month] = $result->total_orders ?? 0;
                }
            }
        }
        // (Tuỳ chọn) trả về dạng JSON cho biểu đồ
        return response()->json($monthlyOrders);
    }

    public function getStatisticNewCustomerByWeek(Request $request)
    {
        $thuHai = Carbon::now()->startOfWeek();
        $chuNhat = Carbon::now()->endOfWeek();
        $data = User::whereNull('deleted_at')->where('created_at', '>=', $thuHai)->where('created_at', '<=', $chuNhat)->count();
        return response()->json(['data' => ['total' => $data]], 200);
    }

    public function updateOrder(UpdateOrderValidator $request, $code)
    {
        $order = Order::whereNull('deleted_at')->where('code', $code)->first();
        $order = $this->model->updateOrder($request, $order);
        return fractal($order, new OrderTransformer())->respond();
    }

    public function updateStatusOrder(UpdateStatusOrderValidator $request, $code)
    {
        $order = Order::whereNull('deleted_at')->where('code', $code)->first();
        $request['order_status_id'] = $request['order_status_id'];
        $order = $this->model->updateOrder($request, $order);
        return fractal($order, new OrderTransformer())->respond();
    }
}
