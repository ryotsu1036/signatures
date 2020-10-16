<?php

namespace App\Http\Controllers\API;

use App\Models\Contract;
use Illuminate\Http\Request;
use App\Services\ContractService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    /**
     * The contract service implementation.
     *
     * @var ContractService
     */
    protected $contractService;

    /**
     * Create a new controller instance.
     *
     * @param  ContractService  $contractService
     * @return void
     */
    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Contract::orderBy('created_at', 'desc')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'consumer_name' => 'required',
            'consumer_phone' => 'required',
            'signature_image' => 'required',
        ])->validate();

        return $this->contractService->create($validatedData);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
