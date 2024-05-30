<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\TempImage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class HomeController extends Controller
{
    public function homeDashboard()
    {
        $totalOrders = Order::where('status', '!=', 'cancelled')->count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('grand_total');
        $totalProducts = Product::count();
        $totalUsers = User::where('role', 1)->count();

        // Current month data
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');
        $totalOrdersThisMonth = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $startOfMonth)
            ->whereDate('created_at', '<=', $currentDate)
            ->count();

        // last month date
        $lastMonth = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $lastMonthName = Carbon::now()->subMonth()->format('F');
        $totalOrdersLastMonth = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $lastMonth)
            ->whereDate('created_at', '<=', $lastMonthEnd)
            ->count();


        // last 30 days sales
        $last30Days = Carbon::now()->subDays(30)->format('Y-m-d');
        $last30DaysSales = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $last30Days)
            ->whereDate('created_at', '<=', $currentDate)
            ->sum('grand_total');


        // delete temp images
        $dayBeforeToday = Carbon::now()->subDays(1)->format('Y-m-d H:i:s');
        // dd($dayBeforeToday);
        $tempImages = TempImage::where('created_at', '<=', $dayBeforeToday)->get();

        foreach ($tempImages as $tempImage) {
            // echo $tempImage->created_at . '===' . $tempImage->id;
            // echo '<br>';

            $path = public_path('temp/' . $tempImage->image);
            $thumbPath = public_path('/temp/thumb/' . $tempImage->image);

            // echo $path . '<br>';
            // echo $thumbPath . '<br>';

            // path delete
            if (File::exists($path)) {
                File::delete($path);
            }

            // thumb path delete
            if (File::exists($thumbPath)) {
                File::delete($thumbPath);
            }

            // delete from database
            TempImage::where('id', $tempImage->id)->delete();
        }

        return view('Admin.dashboard', compact(
            'totalOrders',
            'totalProducts',
            'totalUsers',
            'totalRevenue',
            'totalOrdersThisMonth',
            'totalOrdersLastMonth',
            'last30DaysSales',
            'lastMonthName'
        ));
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
