<?php

namespace App\Exports;

use App\Models\ImportReceipt;
use Illuminate\Contracts\View\View;

class InventoryExport
{
    protected $search;
    protected $year;
    protected $month;

    public function __construct($search = null, $year = null, $month = null)
    {
        $this->search = $search;
        $this->year = $year;
        $this->month = $month;
    }

    public function view(): View
    {
        $query = ImportReceipt::with(['items', 'creator'])->orderBy('import_date', 'desc');

        if ($this->search) {
            $search = trim($this->search);
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhereHas('items', function($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($this->year) {
            $query->whereYear('import_date', $this->year);
        }

        if ($this->month) {
            $query->whereMonth('import_date', $this->month);
        }

        $receipts = $query->get();

        $rows = [];
        $grandTotal = 0;
        $totalItemsCount = 0;

        foreach ($receipts as $receipt) {
            foreach ($receipt->items as $item) {
                $itemAmount = (float)($item->total_amount ?: ($item->quantity * $item->price));
                $grandTotal += $itemAmount;
                $totalItemsCount += (int)$item->quantity;

                $rows[] = [
                    'code' => $receipt->code,
                    'import_date' => $receipt->import_date ? $receipt->import_date->format('d/m/Y') : '',
                    'supplier_name' => $receipt->supplier_name,
                    'supplier_phone' => $receipt->supplier_phone ?: '',
                    'item_name' => $item->name,
                    'item_type' => $item->type ?: 'Nguyên liệu',
                    'unit' => $item->unit ?: 'Kg',
                    'quantity' => (int)$item->quantity,
                    'price' => (float)$item->price,
                    'total_amount' => $itemAmount,
                    'creator' => $receipt->creator_name ?: ($receipt->creator->fullname ?? 'Quản trị viên'),
                    'note' => $receipt->note ?: ''
                ];
            }
        }

        return view('backend.import-goods.export', [
            'rows' => $rows,
            'grandTotal' => $grandTotal,
            'totalItemsCount' => $totalItemsCount,
            'totalReceipts' => $receipts->count(),
            'year' => $this->year,
            'month' => $this->month
        ]);
    }
}
