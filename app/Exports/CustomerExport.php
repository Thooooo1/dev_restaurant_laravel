<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Order;
use Illuminate\Contracts\View\View;

class CustomerExport
{
    protected $q;
    protected $status;

    public function __construct($q = null, $status = null)
    {
        $this->q = $q;
        $this->status = $status;
    }

    public function view(): View
    {
        $query = User::where('roles', 'customer');

        if ($this->q) {
            $q = trim($this->q);
            $query->where(function($sub) use ($q) {
                $sub->where('fullname', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($this->status !== null && $this->status !== '') {
            $query->where('status', (int)$this->status);
        }

        $customers = $query->orderBy('created_at', 'desc')->get();

        // Map spending and order count by phone and user_id
        $phones = $customers->pluck('phone')->filter()->toArray();
        $userIds = $customers->pluck('id')->toArray();

        $orders = Order::whereNull('deleted_at')
            ->where(function($w) use ($userIds, $phones) {
                $w->whereIn('user_id', $userIds)
                  ->orWhereIn('phone', $phones);
            })
            ->get();

        $rows = [];
        $grandTotalSpent = 0;
        $grandTotalOrders = 0;

        foreach ($customers as $c) {
            $custOrders = $orders->filter(function($o) use ($c) {
                return $o->user_id == $c->id || ($c->phone && $o->phone == $c->phone);
            });

            $orderCount = $custOrders->count();
            $totalSpent = (float)$custOrders->whereIn('status', [1, 2])->sum('total_price');

            $grandTotalSpent += $totalSpent;
            $grandTotalOrders += $orderCount;

            $statusText = $c->status == 1 ? 'Hoạt động' : 'Tạm khóa';

            $rows[] = [
                'id' => $c->id,
                'fullname' => $c->fullname ?: $c->username,
                'phone' => $c->phone ?: '',
                'email' => $c->email ?: '',
                'gender' => $c->gender ?: 'Khác',
                'address' => $c->address ?: '',
                'order_count' => $orderCount,
                'total_spent' => $totalSpent,
                'created_at' => $c->created_at ? $c->created_at->format('d/m/Y') : '',
                'status' => $statusText
            ];
        }

        return view('backend.user.export_customers', [
            'rows' => $rows,
            'grandTotalSpent' => $grandTotalSpent,
            'grandTotalOrders' => $grandTotalOrders,
            'totalCustomers' => count($rows)
        ]);
    }
}
