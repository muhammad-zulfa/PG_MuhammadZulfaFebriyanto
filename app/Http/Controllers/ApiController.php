<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function home(Request $request)
    {
        $source1 = Http::get('https://data.jakarta.go.id/read-resource/get-json/daftar-rumah-sakit-rujukan-penanggulangan-covid-19/65d650ae-31c8-4353-a72b-3312fd0cc187');

        $source2 = Http::get('https://data.jakarta.go.id/read-resource/get-json/rsdkijakarta-2017-10/8e179e38-c1a4-4273-872e-361d90b68434');

        $rsRujukan = $source1->json();
        $rsDki = $source2->json();

        $data = collect($rsRujukan)->map(function ($item, $key) use($rsDki) {
            $detail = [];

            foreach ($rsDki as $data){
                if($item['kelurahan'] == strtoupper($data['kelurahan'])) {
                    $detail = $data;
                }
            }

            if(count($detail) > 0) {
                $item = $this->mapDetailData($item, $detail);
            }

            return $item;
        });

        return response()->json($data);
    }

    public function filter(Request $request)
    {
        $paramType = '';
        $filterParam = '';
        if($request->kelurahan) {
            $filterParam = $request->get('kelurahan');
            $paramType = 'kelurahan';
        } elseif ($request->kecamatan) {
            $filterParam = $request->get('kecamatan');
            $paramType = 'kecamatan';
        }
        elseif ($request->kota_madya) {
            $filterParam = $request->get("kota_madya");
            $paramType = 'kota_madya';
        }

        $source1 = Http::get('https://data.jakarta.go.id/read-resource/get-json/daftar-rumah-sakit-rujukan-penanggulangan-covid-19/65d650ae-31c8-4353-a72b-3312fd0cc187');

        $source2 = Http::get('https://data.jakarta.go.id/read-resource/get-json/rsdkijakarta-2017-10/8e179e38-c1a4-4273-872e-361d90b68434');

        $rsRujukan = $source1->json();
        $rsDki = $source2->json();

        if($paramType != '') {
            $rsRujukan = collect($rsRujukan)->filter(function ($item, $key) use($filterParam, $paramType) {
                return $item[$paramType] == $filterParam;
            })->values();
        }

        $data = collect($rsRujukan)->map(function ($item, $key) use($rsDki) {
            $detail = [];

            foreach ($rsDki as $data){
                if($item['kelurahan'] == strtoupper($data['kelurahan'])) {
                    $detail = $data;
                }
            }

            if(count($detail) > 0) {
                $item = $this->mapDetailData($item, $detail);
            }

            return $item;
        });

        return response()->json($data);
    }

    private function mapDetailData($object, $detail){
        $object['kode_pos'] = $detail['kode_pos'];
        $object['nomor_telepon'] = $detail['nomor_telepon'];
        $object['nomor_fax'] = $detail['nomor_fax'];
        $object['no_hp_direktur/kepala_rs'] = $detail['no_hp_direktur/kepala_rs'];
        $object['website'] = $detail['website'];
        $object['email'] = $detail['email'];

        return $object;
    }
}
