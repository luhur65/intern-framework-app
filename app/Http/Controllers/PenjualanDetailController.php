<?php

namespace App\Http\Controllers;

use App\Models\PenjualanDetail;
use Illuminate\Http\Request;

class PenjualanDetailController extends Controller
{
    protected $gridParams = [];
    protected $filters = [];

    public function __construct()
    {
        $this->gridParams = [
            'sidx'   => request()->input('sidx', 'id'),
            'sord'   => request()->input('sord', 'asc'),
            'page'   => (int) request()->input('page', 1),
            'limit'  => (int) request()->input('rows', 10),
        ];

        $this->gridParams['global_search'] = request()->input('global_search', '');
        $this->gridParams['_search'] = request()->input('_search', 'false');

        if (request()->has('_search') && request()->input('_search') == 'true') {
            $filterJson = request()->input('filters');
            $decoded = json_decode($filterJson, true);
            if (isset($decoded['rules'])) {
                $this->filters = $decoded['rules'];
            }

            $this->gridParams['filters'] = $this->filters;
        }

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
