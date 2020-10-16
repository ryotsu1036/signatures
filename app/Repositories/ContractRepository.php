<?php

namespace App\Repositories;

use App\Models\Contract;

class ContractRepository
{
    protected $contract;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function all()
    {
        return $this->contract->all();
    }

    public function create($data = [])
    {
        $contract = new $this->contract;

        $contract->consumer_name = $data['consumer_name'];
        $contract->consumer_phone = $data['consumer_phone'];
        $contract->signature_image = $data['signature_image'];
        $contract->document = $data['document'];

        $contract->save();

        return response()->json([
            'type' => 'success',
            'status' => true,
            'message' => '已成功新增一筆資料！'
        ]);
    }
}
