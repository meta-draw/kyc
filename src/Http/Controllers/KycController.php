<?php

namespace MetaDraw\Kyc\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MetaDraw\Kyc\Services\KycService;
use MetaDraw\Kyc\Http\Requests\VerifyKycRequest;

class KycController extends Controller
{
    public function __construct(private KycService $kycService)
    {
    }

    public function verify(VerifyKycRequest $request)
    {
        $result = $this->kycService->verify(
            $request->input('id_card'),
            $request->input('mobile'),
            $request->input('real_name'),
            $request->user()->id
        );

        return response()->json($result);
    }

    public function status(Request $request)
    {
        $result = $this->kycService->status($request->user()->id);

        return response()->json($result);
    }
}