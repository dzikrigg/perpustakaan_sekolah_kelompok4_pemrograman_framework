<?php

namespace App\Http\Controllers\Headmaster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Config;
use DB;
use Exception;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $denda = Config::where('name', 'denda')->first()->value ?? 0;

        $items = Transaction::with(['book', 'siswa'])->filter($request)->orderBy('status', 'asc')->orderBy('tanggal_kembali')->paginate(10);
        
        $items->getCollection()->transform(function ($item) use ($denda) {
            $keterlambatan_hari = $item->tanggal_pinjam->gt($item->tanggal_kembali) 
                                ? $item->tanggal_pinjam->diff($item->tanggal_kembali)->days 
                                : 0;

            $item->keterlambatan = '<span class="badge badge-' . ($keterlambatan_hari > 0 ? "danger" : "success") . '">' . $keterlambatan_hari . ' hari</span>';
            $item->denda = '<span class="badge badge-' . ($keterlambatan_hari > 0 ? "danger" : "success") . '">Rp. ' . ($keterlambatan_hari * $denda) . '</span>';

            return $item;
        });

        $view = [
            'items' => $items
        ];

        return view('headmaster.transaction.index')->with($view);
    }
}
