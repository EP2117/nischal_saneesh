<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class DailyPurchaseExport implements FromView, WithTitle
{
    protected $purchase;

    public function __construct($purchase)
    {
        $this->purchase = $purchase;
    }

    public function view(): View
    {

        return view('exports.daily_purchase', [
            'purchase' => $this->purchase,
        ]);
    }

    public function title(): string
    {
        return 'Daily Purchase Report';
    }
}
