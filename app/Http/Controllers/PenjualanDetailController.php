<?php

namespace App\Http\Controllers;

use App\Models\PenjualanDetail;
use Illuminate\Http\Request;

/**
 * Class PenjualanDetailController
 *
 * Handles requests related to the details of a sales transaction. This controller
 * is primarily used to fetch data for the detail grid in the sales view.
 *
 * @package App\Http\Controllers
 */
class PenjualanDetailController extends Controller
{
    /**
     * @var array Holds the parameters for the jqGrid.
     */
    protected $gridParams = [];

    /**
     * @var array Holds the filter rules from the request.
     */
    protected $filters = [];

    /**
     * PenjualanDetailController constructor.
     *
     * Initializes grid parameters from the current request, including settings for
     * sorting, pagination, and search filters, which are used for the detail grid.
     */
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

    /**
     * Display a listing of the resource.
     *
     * This method is not currently used but is kept for potential future
     * implementation of a dedicated detail index view.
     */
    public function index()
    {   
        
    }

    /**
     * Get detail data for a specific sales transaction.
     *
     * This method fetches the line items (details) for a given sales ID. It
     * applies any filtering, sorting, and pagination parameters from the request
     * and returns the data as a JSON response for the detail jqGrid.
     *
     * @param int $penjualanId The ID of the parent sales transaction.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the detail data.
     */
    public function getDetail($penjualanId)
    {
        $params = $this->gridParams;
        $params['penjualan_id'] = $penjualanId;
        $params['global_search'] = request()->input('global_search', '');
        $params['_search'] = request()->input('_search', 'false');

        if (request()->has('_search') && request()->input('_search') == 'true') {
            $filterJson = request()->input('filters');
            $decoded = json_decode($filterJson, true);
            if (isset($decoded['rules'])) {
                $this->filters = $decoded['rules'];
            }

            $params['filters'] = $this->filters;
        }
        $data = PenjualanDetail::getGridDetail($params);
        return \response()->json($data);

        // return "hello";
    }
}