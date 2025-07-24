<?php

namespace App\Http\Controllers;

use App\Models\PenjualanDetail;
use Illuminate\Http\Request;

class PenjualanDetailController extends Controller
{
    protected $gridParams = [];

    public function __construct(Request $request)
    {
        $this->gridParams = [
            'sidx'   => $request->input('sidx', 'id_detail'),
            'sord'   => $request->input('sord', 'asc'),
            'page'   => (int) $request->input('page', 1),
            'limit'  => (int) $request->input('rows', 10),
        ];

    }

    public function index()
    {   
        
    }

    /**
     * Get detail data for Penjualan.
     *
     * @param int $penjualanId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($penjualanId)
    {
        $params = $this->gridParams;
        $params['penjualan_id'] = $penjualanId;
        $data = PenjualanDetail::getGridDetail($params);
        return \response()->json($data);

        // return "hello";
    }
}
